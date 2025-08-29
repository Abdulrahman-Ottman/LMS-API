<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Section;
use App\Models\Lesson;
use App\Models\Instructor;

class CoursesTableSeeder extends Seeder
{
    public function run()
    {
        $instructors = Instructor::pluck('id')->all();

        if (count($instructors) < 1) {
            $this->command->error('Seed instructors before seeding courses.');
            return;
        }

        // عناوين واقعية مختصرة للدورات
        $courseTitles = [
            'Intro to Programming', 'Web Development Basics', 'Mobile Apps with Flutter',
            'Object-Oriented Design', 'Data Structures & Algorithms', 'Database Design Fundamentals',
            'RESTful API Development', 'Cyber Security Essentials', 'Linux Administration',
            'Cloud Computing Overview', 'Machine Learning Starter', 'AI Concepts for Beginners',
            'Networks & Protocols', 'Software Testing Practices', 'Agile & Scrum in Practice',
            'DevOps Crash Course', 'Frontend with HTML/CSS/JS', 'Backend with PHP & Laravel',
            'Data Analysis Essentials', 'Project Management Basics',
        ];

        for ($i = 1; $i <= 20; $i++) {

            $course = Course::create([
                'instructor_id'  => $instructors[array_rand($instructors)],
                'title'          => $courseTitles[$i - 1],
                'image'          => 'storage/public/images/courses/default.png',
                'views'          => rand(250, 8000),
                'description'    => 'Comprehensive course covering fundamentals with practical examples and exercises in Arabic.',
                'price'          => (float) rand(50, 200),  // float كما في الجدول
                'level'          => rand(1, 3),             // 1: مبتدئ، 2: متوسط، 3: متقدم
                'rating'         => rand(0, 5),             // تقييم عددي
                'discount'       => number_format(rand(0, 5000) / 100, 2), // 0.00 إلى 50.00
                'enabled'        => true,                   // الحقل المُضاف لاحقاً
                'total_duration' => 0,                      // سنحسبه بعد إضافة الدروس
            ]);

            $courseTotalDuration = 0;

            // 7 أقسام لكل دورة
            for ($s = 1; $s <= 7; $s++) {

                $section = Section::create([
                    'title'          => "Section {$s} - " . $course->title,
                    'course_id'      => $course->id,
                    'order'          => $s,
                    'total_duration' => 0, // الحقل المُضاف لاحقاً
                ]);

                $sectionTotal = 0;

                // 3 دروس ثابتة لكل قسم
                $lessons = [
                    ['title' => 'Introduction', 'file_name' => 'intro.mp4'],
                    ['title' => 'Lesson 1',     'file_name' => 'lesson1.mp4'],
                    ['title' => 'Lesson 2',     'file_name' => 'lesson2.mp4'],
                ];

                foreach ($lessons as $index => $def) {
                    // المدة (بالدقائق) رقم صحيح كما في الجدول
                    $duration = rand(8, 20); // 8 إلى 20 دقيقة

                    Lesson::create([
                        'title'      => $def['title'],
                        'section_id' => $section->id,
                        'duration'   => $duration,                 // موجود كما طلبت
                        'file_name'  => $def['file_name'],         // اسم الملف كما هو مطلوب
                        'order'      => $index + 1,
                        'views'      => rand(0, 1200),
                    ]);

                    $sectionTotal += $duration;
                }

                // تحديث مدة القسم الإجمالية
                $section->update([
                    'total_duration' => $sectionTotal,
                ]);

                $courseTotalDuration += $sectionTotal;
            }

            // تحديث مدة الدورة الإجمالية
            $course->update([
                'total_duration' => $courseTotalDuration,
            ]);
        }
    }
}
