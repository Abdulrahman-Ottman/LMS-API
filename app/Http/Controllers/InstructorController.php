<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Instructor;
use App\Models\InstructorRating;
use App\Models\InstructorCategory;

class InstructorController extends Controller
{
    public function getInstructors(Request $request)
    {
        $instructorsQuery = Instructor::query()->with('categories');
        if ($categoryNames = $request->get('category')) {
            $instructorsQuery->whereHas('categories', function ($query) use ($categoryNames) {
                $query->whereIn('name', $categoryNames);
            });
        }
        switch ($request->get('sort_by')) {
            case 'views_asc':
                $instructorsQuery->orderBy('views', 'asc');
                break;
            case 'views_desc':
                $instructorsQuery->orderBy('views', 'desc');
                break;
            case 'rating':
                $instructorsQuery->orderBy('rating', 'desc');
                break;
        }
        $instructors = $instructorsQuery->paginate(10);
        $instructors->appends($request->query());
        return response()->json([
            'current_page' => $instructors->currentPage(),
            'data' => $instructors,
            'links' => [
                'previous' => $instructors->previousPageUrl(),
                'next' => $instructors->nextPageUrl(),
            ],
            'per_page' => $instructors->perPage(),
            'total' => $instructors->total(),
        ]);
    }
    public function show($id)
    {
        $instructor = Instructor::with(['categories','courses'])
            ->findOrFail($id);
        $enrolledCount = 0;
        $completedCount = 0;

        foreach ($instructor->courses as $course) {
            $enrolledCount  += $course->students()->where('status', 'enrolled')->count();
            $completedCount += $course->students()->where('status', 'completed')->count();
        }

        return response()->json([
            'data' => $instructor,
            'students' => [
                'enrolled'  => $enrolledCount,
                'completed' => $completedCount,
            ],
        ], 200);
    }

    public function rate(Request $request, $id)
    {
        Instructor::findOrFail($id);
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);
        // dd(auth()->user()->student->id, $id,$request->rating);

        InstructorRating::updateOrCreate(
            [
                'instructor_id' => $id,
                'student_id' =>auth()->user()->student->id,
            ],
            ['rating' => $request->rating]
        );

        $averageRating = InstructorRating::where('instructor_id', $id)->average('rating');
        Instructor::where('id', $id)->update(['rating' => $averageRating]);

        return response()->json([
            'message' => 'Rating submitted successfully!',
            'rating' => $averageRating,
        ]);
    }

    public function addView($id)
    {
        $instructor = Instructor::findOrFail($id);
        $instructor->increment('views');

        return response()->json([
            'message'           => 'View recorded successfully',
            'instructor_views'  => $instructor->views,
        ], 200);
    }
}
