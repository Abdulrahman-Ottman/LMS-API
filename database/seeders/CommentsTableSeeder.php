<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lesson;
use App\Models\Section;
use App\Models\Course;
use App\Models\Comment;
use Illuminate\Support\Facades\DB;

class CommentsTableSeeder extends Seeder
{
    public function run()
    {
        $lessons = Lesson::all();

        foreach ($lessons as $lesson) {
            // نجيب الكورس المرتبط بهذا الدرس
            $courseId = Section::where('id', $lesson->section_id)->value('course_id');

            // نجيب الطلاب الملتحقين بالكورس (enrolled أو completed)
            $students = DB::table('course_student')
                ->where('course_id', $courseId)
                ->whereIn('status', ['enrolled', 'completed'])
                ->pluck('student_id')
                ->toArray();

            if (count($students) < 1) {
                continue; // مافي طلاب، نكمل للدرس اللي بعده
            }

            // نختار 8 طلاب (أو أقل إذا العدد أقل)
            $randomStudents = collect($students)->random(min(8, count($students)));

            foreach ($randomStudents as $studentId) {
                Comment::create([
                    'lesson_id'  => $lesson->id,
                    'student_id' => $studentId,
                    'body'       => fake()->sentence(rand(8, 15)), // تعليق نصي قصير
                    'created_at' => now()->subDays(rand(0, 60)),   // بين اليوم و60 يوم سابق
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
