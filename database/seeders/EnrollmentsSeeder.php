<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Support\Facades\DB;

class EnrollmentsSeeder extends Seeder
{
    public function run()
    {
        $students = Student::all();
        $courses  = Course::all();

        foreach ($students as $student) {
            // اختيار 15 كورس عشوائي
            $randomCourses = $courses->random(15);

            // تقسيم إلى 3 مجموعات
            $wishlist  = $randomCourses->slice(0, 5);
            $enrolled  = $randomCourses->slice(5, 5);
            $completed = $randomCourses->slice(10, 5);

            // wishlist
            foreach ($wishlist as $course) {
                DB::table('course_student')->insert([
                    'student_id' => $student->id,
                    'course_id'  => $course->id,
                    'rating'     => null,
                    'status'     => 'wishlist',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // enrolled
            foreach ($enrolled as $course) {
                DB::table('course_student')->insert([
                    'student_id' => $student->id,
                    'course_id'  => $course->id,
                    'rating'     => null,
                    'status'     => 'enrolled',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // completed (مع تقييم)
            foreach ($completed as $course) {
                $rating = rand(3, 5);

                DB::table('course_student')->insert([
                    'student_id' => $student->id,
                    'course_id'  => $course->id,
                    'rating'     => $rating,
                    'status'     => 'completed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // إضافة review للكورس (كل طالب أنهى يضيف review)
                DB::table('course_reviews')->insert([
                    'student_id' => $student->id,
                    'course_id'  => $course->id,
                    'review'     => fake()->sentence(12),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // جلب جميع دروس الكورس وتسجيلها في lesson_student
                $lessons = Lesson::whereHas('section', function ($q) use ($course) {
                    $q->where('course_id', $course->id);
                })->get();

                foreach ($lessons as $lesson) {
                    DB::table('lesson_student')->insert([
                        'lesson_id'  => $lesson->id,
                        'student_id' => $student->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
