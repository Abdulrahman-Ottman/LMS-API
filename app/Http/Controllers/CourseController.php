<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Traits\FilterCourses;
use App\Traits\SortCourses;
use App\Models\CourseStudent;
use App\Models\CourseReview;


class CourseController extends Controller
{
    use filterCourses, sortCourses;
    public function show($id)
    {
        $course = Course::with(['instructor', 'categories', 'reviews'])->find($id);
        if (!$course) {
            return response()->json(['message' => 'Course not found.'], 404);
        }
        return response()->json(['data' => $course]);
    }

    public function getCourses(Request $request)
    {
        $coursesQuery = Course::select('id', 'title', 'description', 'price', 'level', 'instructor_id','views','image','created_at','rating','discount')
            ->with('instructor');
        $this->filterCourses($request, $coursesQuery);

        $sortBy = $request->get('sort_by');
        if ($sortBy) {
            $this->sortCourses($sortBy, $coursesQuery);
        }

        $courses = $coursesQuery->paginate(10);
        $courses->appends($request->query());

        if ($courses->isEmpty()) {
            return response()->json(['message' => 'No courses available.'], 404);
        }

        return response()->json([
            'current_page' => $courses->currentPage(),
            'data' => $courses,
            'links' => [
                'previous' => $courses->previousPageUrl(),
                'next' => $courses->nextPageUrl(),
            ],
            'per_page' => $courses->perPage(),
            'total' => $courses->total(),
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user->isInstructor()||!$user->instructor->verified) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:png,jpg',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'level' => 'integer|nullable',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
            'discount' => 'numeric|min:0|max:100',
        ]);

        $path = $request->file('image')->store('images/course-images', 'public');
        $path = 'storage/' . str_replace("public/", "", $path);

        $course = Course::create([
            'instructor_id' => $user->instructor->id,
            'title' => $request->title,
            'image' => $path,
            'description' => $request->description,
            'price' => $request->price,
            'level' => $request->level,
            'views' => 0,
            'discount' => $request->discount ?? 0.00,
        ]);

        foreach ($request->category_ids as $categoryId) {
            $category = Category::find($categoryId);
            if ($category) {
                $course->categories()->attach($categoryId);

                if (!$user->instructor->categories()->where('category_id', $categoryId)->exists()) {
                    $user->instructor->categories()->attach($categoryId);
                }

                if ($category->parent_id) {
                    $course->categories()->attach($category->parent_id);

                    if (!$user->instructor->categories()->where('category_id', $category->parent_id)->exists()) {
                        $user->instructor->categories()->attach($category->parent_id);
                    }
                }
            }
        }
        return response()->json(['message' => 'Course added successfully!'], 201);
    }

    public function update(Request $request, $id)
    {
        $course = Course::with('categories')->findOrFail($id);

        if ($course->instructor_id !== auth()->user()->instructor->id) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $request->validate([
            'title' => 'string|max:255|nullable',
            'image' => 'image|mimes:png,jpg|nullable',
            'description' => 'string|nullable',
            'price' => 'numeric|nullable',
            'level' => 'integer|nullable',
            'category_ids' => 'array',
            'category_ids.*' => 'exists:categories,id',
            'discount' => 'numeric|min:0|max:100|nullable',
        ]);

        $updateData = $request->only(['title', 'description', 'price', 'level', 'discount']);
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images/course-images', 'public');
            $path = 'storage/' . str_replace("public/", "", $path);
            $updateData['image'] = $path;
        }

        $course->update($updateData);
        $newCategoryIds = $request->category_ids;
        if($newCategoryIds){
        $oldCategoryIds = $course->categories->pluck('id');
        $course->categories()->sync($newCategoryIds);
        $instructor = auth()->user()->instructor;
        $instructor->categories()->syncWithoutDetaching($newCategoryIds);

        $unusedCategories = $instructor->categories()
            ->whereIn('categories.id', $oldCategoryIds)
            ->whereDoesntHave('courses', function ($query) use ($instructor) {
            $query->where('courses.instructor_id', $instructor->id);
        })->pluck('categories.id');

        $instructor->categories()->detach($unusedCategories);

        foreach ($newCategoryIds as $categoryId) {
            $category = Category::find($categoryId);
            if ($category) {
                if ($category->parent_id) {
                    if (!$course->categories()->where('categories.id', $category->parent_id)->exists()) {
                        $course->categories()->attach($category->parent_id);
                    }

                    if (!$instructor->categories()->where('categories.id', $category->parent_id)->exists()) {
                        $instructor->categories()->attach($category->parent_id);
                    }
                }
            }
        }
        }

        return response()->json(['message' => 'Course updated successfully!']);
    }

    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        if (($course->instructor_id != auth()->user()->instructor->id)&&!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }
        $instructor = $course->instructor;
        $courseId = $course->id;

         $unusedCategoriesIds = Category::whereHas('courses', function($query) use ($instructor, $courseId) {
            $query->where('instructor_id', $instructor->id)
                  ->where('courses.id', '!=', $courseId);
        })
        ->pluck('id');

        $oldCategoriesIds = $course->categories->pluck('id');

        $instructor->categories()->detach($oldCategoriesIds);
        $instructor->categories()->attach($unusedCategoriesIds);
        $course->delete();

        return response()->json(['message' => 'Course deleted successfully.'], 204);
    }
    public function addView($id)
    {
        $course = Course::findOrFail($id);
        $course->increment('views');
        $instructorViews = $course->instructor->increment('views');

        return response()->json([
            'message'           => 'View recorded successfully',
            'course_views'      => $course->views,
            'instructor_views'  => $instructorViews,
        ], 200);
    }
    public function rate(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);
        CourseStudent::updateOrCreate(
            [
                'student_id' => auth()->user()->student->id,
                'course_id' => $id
            ],
            ['rating' => $request->rating]
        );
        $averageRating = CourseStudent::where('course_id', $id)->average('rating');
        Course::where('id', $id)->update(['rating' => $averageRating]);

        return response()->json([
            'message' => 'Course rated successfully!',
            'rating' => $averageRating,
        ], 200);
    }
    public function review(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $request->validate([
            'review' => 'required|string',
        ]);
        CourseReview::create([
            'student_id' => auth()->user()->student->id,
            'course_id' => $id,
            'review' => $request->review,
        ]);
        return response()->json(['message' => 'Course reviewed successfully!']);
    }
}
