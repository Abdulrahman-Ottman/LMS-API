<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LessonController extends Controller
{
    // Store a new lesson
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'section_id' => 'required|exists:sections,id',
            'duration' => 'required|numeric|min:0',
            'video' => 'required|file|mimes:mp4,mov,ogg,webm|max:51200', // 50MB max
        ]);

        // Save to local storage in storage/app/videos
        $path = $request->file('video')->store('videos');

        $lesson = Lesson::create([
            'title' => $request->title,
            'section_id' => $request->section_id,
            'duration' => $request->duration,
            'video_url' => $path, // e.g. videos/filename.mp4
        ]);

        return response()->json($lesson, 201);
    }

    // Show one lesson
    public function show($id)
    {
        //get comments
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
                'id' => $lesson->section->course->instructor->id ?? null,
                'name' => $lesson->section->course->instructor->full_name ?? null,
            ],
        ]);
    }


    // Update lesson
    public function update(Request $request, $id)
    {
        $lesson = Lesson::findOrFail($id);

        $request->validate([
            'title' => 'sometimes|string',
            'section_id' => 'sometimes|exists:sections,id',
            'duration' => 'sometimes|numeric|min:0',
            'script' => 'nullable|string',
            'video' => 'sometimes|file|mimes:mp4,mov,ogg,webm|max:51200',
        ]);

        if ($request->hasFile('video')) {
            // Delete old video from local storage
            if ($lesson->video_url && Storage::disk('local')->exists($lesson->video_url)) {
                Storage::disk('local')->delete($lesson->video_url);
            }

            // Save new video
            $lesson->video_url = $request->file('video')->store('videos');
        }

        $lesson->update($request->only(['title', 'section_id', 'duration', 'script', 'video_url']));

        return response()->json($lesson);
    }

    // Delete lesson
    public function destroy($id)
    {
        $lesson = Lesson::findOrFail($id);

        if ($lesson->video_url && Storage::disk('local')->exists($lesson->video_url)) {
            Storage::disk('local')->delete($lesson->video_url);
        }

        $lesson->delete();

        return response()->json(['message' => 'Lesson deleted']);
    }


    public function completeLesson(Request $request, $LessonId)
    {
        // Assuming the user is authenticated and linked to a student
        $student = auth()->user()->student ?? null;

        if (!$student) {
            return response()->json(['message' => 'Student is not authorized.'], 403);
        }

        // Check if the lesson is already marked as completed
        $alreadyCompleted = LessonStudent::where('lesson_id', $LessonId)
            ->where('student_id', $student->id)
            ->exists();

        if ($alreadyCompleted) {
            return response()->json(['message' => 'This lesson has already been completed.'], 200);
        }

        // Mark lesson as completed
        LessonStudent::create([
            'lesson_id' => $LessonId,
            'student_id' => $student->id,
        ]);

        return response()->json(['message' => 'Lesson marked as completed successfully.'], 201);
    }
}
