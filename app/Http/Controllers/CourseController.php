<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Section;
use Illuminate\Http\Request;
use App\Traits\FilterCourses;
use App\Traits\SortCourses;
use App\Models\CourseStudent;
use App\Models\CourseReview;
use Illuminate\Support\Facades\Storage;


class CourseController extends Controller
{
    use filterCourses, sortCourses;
    public function show($id)
    {
$course = Course::with([
        'instructor',
        'categories',
        'reviews.student.user:id,user_name,avatar',
        'sections.lessons'
    ])->find($id);
        if (!$course) {
            return response()->json(['message' => 'Course not found.'], 404);
        }
        $course->status = $course->students()
            ->where('student_id', auth()->user()->student->id)
            ->pluck('status')
            ->first();
        unset($course->students);


    $sectionCount = $course->sections->count();
    $lessonCount = $course->sections->flatMap->lessons->count();
    $totalDuration = $course->sections->flatMap->lessons->sum('duration');
        return response()->json([
        'data' => $course ,
        'meta' => [
            'section_count' => $sectionCount,
            'lesson_count' => $lessonCount,
            'total_duration' => $totalDuration
        ]]);
    }

    public function getCourses(Request $request)
    {
        $coursesQuery = Course::select('id', 'title', 'description', 'price', 'level', 'instructor_id','views','image','created_at','rating','discount')
            ->with(['instructor','categories:id,name']);
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

        $courses->getCollection()->transform(function ($course) {
            $course->status = $course->students()
                ->where('student_id', auth()->user()->student->id)
                ->pluck('status')
                ->first();
            unset($course->students);
            return $course;
        });

        return response()->json($courses);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user->instructor->verified) {
            return response()->json(['message' => 'Not verified account.'], 403);
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
        $course = Course::with('sections.lessons')->findOrFail($id);
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


        foreach ($course->sections as $section) {
            foreach ($section->lessons as $lesson) {
                $path = 'videos/' .$lesson->file_name;
                if (Storage::disk('local')->exists($path)) {
                    Storage::disk('local')->delete($path);
                    $prefix = pathinfo($lesson->file_name, PATHINFO_FILENAME);
                    $subtitleFiles = Storage::disk('local')->files('subtitles');
                    foreach ($subtitleFiles as $file) {
                        $filename = basename($file);
                        if (strpos($filename, $prefix . '-') === 0 && substr($filename, -4) === '.vtt') {
                            Storage::disk('local')->delete($file);
                        }
                    }
                }
            }
        }
        $course->delete();

        return response()->json(['message' => 'Course deleted successfully.'], 200);
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
        Course::findOrFail($id);
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
    public function updateSectionsOrder(Request $request, $courseId)
    {
        $course = Course::with('sections')->findOrFail($courseId);
        if (auth()->user()->id !== $course->instructor->user_id) {
            return response()->json( ['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|distinct',
        ]);

        $existingIds = $course->sections->pluck('id')->toArray();
        $newOrder = $request->order;
        sort($existingIds);
        sort($newOrder);
        if ($existingIds != $newOrder) {
            return response()->json(['error' => 'Order array must contain all section IDs.'], 422);
        }

        foreach ($request->order as $index => $sectionId) {
            Section::where('id', $sectionId)->update(['order' => $index + 1]);
        }

        return response()->json(['message' => 'Sections reordered successfully']);
    }

    public function disable(Course $course)
    {
        $course->update(['enabled' => false]);

        return response()->json([
            'message' => 'Course disabled successfully.',
            'course'  => $course
        ]);
    }

    public function disableAll(Instructor $instructor)
    {
        $updated = $instructor->courses()->update(['enabled' => false]);

        return response()->json([
            'message' => "All courses for instructor {$instructor->id} disabled successfully.",
            'updated_count' => $updated
        ]);
    }
}
