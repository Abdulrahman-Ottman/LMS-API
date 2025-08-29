<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Support\Facades\DB;

class PivotTablesSeeder extends Seeder
{
    public function run()
    {
        $categories   = Category::whereNull('parent_id')->pluck('id')->all(); // الفئات الرئيسية فقط
        $instructors  = Instructor::with('user')->get();
        $students     = Student::with('user')->get();
        $courses      = Course::all();

        // ---- category_instructor ----
        foreach ($instructors as $instructor) {
            $randomCats = collect($categories)->random(5);

            foreach ($randomCats as $catId) {
                DB::table('category_instructor')->insert([
                    'category_id'   => $catId,
                    'instructor_id' => $instructor->id,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            // ---- course_category (تناسق: دورة المدرس مع فئات المدرس) ----
            $instCourses = $courses->where('instructor_id', $instructor->id);

            foreach ($instCourses as $course) {
                $courseCat = $randomCats->random(); // اختيار واحدة من فئات المدرس
                DB::table('course_category')->insert([
                    'course_id'   => $course->id,
                    'category_id' => $courseCat,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }

        // ---- category_student ----
        foreach ($students as $student) {
            $randomCats = collect($categories)->random(5);

            foreach ($randomCats as $catId) {
                DB::table('category_student')->insert([
                    'category_id' => $catId,
                    'student_id'  => $student->id,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }
}
