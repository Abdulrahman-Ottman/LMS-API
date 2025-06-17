<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use FFMpeg\FFMpeg;

class LessonController extends Controller
{
    public function store(Request $request, FFMpeg $ffmpeg)
    {
        $request->validate([
            'title' => 'required|string',
            'section_id' => 'required|exists:sections,id',
            'video' => 'required|file|mimes:mp4,mov,ogg,webm|max:512000',
        ]);

        $path = $request->file('video')->store('videos');
        $filename = basename($path);
        $video = $ffmpeg->open(storage_path('app/private/' . $path));
        $duration = (int)$video->getFormat()->get('duration');

        $lesson = Lesson::create([
            'title' => $request->title,
            'section_id' => $request->section_id,
            'duration' => $duration,
            'file_name' => $filename,
        ]);
        return response()->json($lesson, 201);
    }

    public function show($id)
    {
        $lesson = Lesson::with([
            'section.course.instructor:id,name'
        ])->find($id);

        if (!$lesson) {
            return response()->json(['message' => 'lesson not found'], 404);
        }

        return response()->json([
            'id' => $lesson->id,
            'title' => $lesson->title,
            'section_id' => $lesson->section_id,
            'file_name' => $lesson->file_name,
            'duration' => $lesson->duration,
            'instructor' => [
                'id' => $lesson->section->course->instructor->id,
                'name' => $lesson->section->course->instructor->full_name,
            ],
        ]);
    }

    public function update(Request $request, $id, FFMpeg $ffmpeg)
    {
        $lesson = Lesson::findOrFail($id);

        $request->validate([
            'title' => 'sometimes|string',
            'section_id' => 'sometimes|exists:sections,id',
            'video' => 'sometimes|file|mimes:mp4,mov,ogg,webm|max:512000',
        ]);

        $dataToUpdate = $request->only(['title', 'section_id']);

        if ($request->hasFile('video')) {
            $path = 'videos/' .$lesson->file_name;
            if (Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }

            $path = $request->file('video')->store('videos');
            $dataToUpdate['file_name']  = basename($path);
            $video = $ffmpeg->open(storage_path('app/private/' . $path));
            $dataToUpdate['duration']  = (int)$video->getFormat()->get('duration');

        }
        $lesson->update($dataToUpdate);

        return response()->json($lesson);
    }

    public function destroy($id)
    {
        $lesson = Lesson::findOrFail($id);
        $path = 'videos/' .$lesson->file_name;
        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
        $lesson->delete();
        return response()->json(['message' => 'Lesson deleted']);
    }

    public function completeLesson(Request $request, $lessonId)
    {
        $student = auth()->user()->student;

        $alreadyCompleted = LessonStudent::where('lesson_id', $lessonId)
            ->where('student_id', $student->id)
            ->exists();

        if ($alreadyCompleted) {
            return response()->json(['message' => 'This lesson has already been completed.'], 200);
        }

        LessonStudent::create([
            'lesson_id' => $lessonId,
            'student_id' => $student->id,
        ]);

        $lesson = Lesson::findOrFail($lessonId);
        $course = $lesson->section->course()->with('sections.lessons')->first();
        $allLessons = $course->sections->flatMap->lessons;

        $completedLessonsCount = LessonStudent::whereIn('lesson_id', $allLessons->pluck('id'))
            ->where('student_id', $student->id)
            ->count();

        if ($completedLessonsCount === $allLessons->count()) {
            CourseStudent::where('student_id', $student->id)
                ->where('course_id', $course->id)
                ->update(['status' => 'completed']);
        }

        return response()->json(['message' => 'Lesson marked as completed successfully.'], 201);
    }
}
