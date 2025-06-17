<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{

    public function getAllSections($courseId)
    {
        $student = auth()->user()->student;

        $sections = Section::where('course_id', $courseId)
            ->with(['lessons' => function ($query) use ($student) {
                $query->with(['students' => function ($q) use ($student) {
                    $q->where('student_id', $student->id);
                }]);
            }])
            ->get();

        $sections->each(function ($section) use ($student) {
            $section->lessons->each(function ($lesson) use ($student) {
                $lesson->completed = $lesson->students->isNotEmpty();
                unset($lesson->students);
            });
        });

        return response()->json($sections);
    }
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'course_id' => 'required|exists:courses,id',
        ]);

        $section = Section::create([
            'title' => $request->title,
            'course_id' => $request->course_id,
        ]);

        return response()->json(['message' => 'Section created successfully', 'section' => $section], 201);
    }

    public function update(Request $request, $id)
    {
        $section = Section::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $section->update([
            'title' => $request->title,
        ]);

        return response()->json(['message' => 'Section updated successfully', 'section' => $section]);
    }

    public function destroy($id)
    {
        $section = Section::findOrFail($id);
        $section->delete();

        return response()->json(['message' => 'Section deleted successfully']);
    }

    public function lessons($id)
    {
        $section = Section::with('lessons')->findOrFail($id);

        return response()->json($section->lessons);
    }
}
