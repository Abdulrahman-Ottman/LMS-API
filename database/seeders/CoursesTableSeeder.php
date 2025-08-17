<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\Instructor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CoursesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coursesData = [
            'Introduction to Programming' => 'Learn the basics of programming, including variables, control structures, and data types.',
            'Advanced Web Development' => 'Dive deeper into web development with frameworks, APIs, and responsive design.',
            'Data Science Essentials' => 'Explore the fundamentals of data analysis, visualization, and machine learning.',
            'Machine Learning Basics' => 'Understand the principles of machine learning and how to apply them to real-world problems.',
            'UI/UX Design Principles' => 'Discover the core principles of user interface and user experience design.',
            'Digital Marketing Strategies' => 'Learn effective strategies for online marketing, SEO, and social media.',
            'Cybersecurity Fundamentals' => 'Gain insights into cybersecurity threats and how to protect information systems.',
            'Mobile App Development' => 'Create mobile applications for iOS and Android using popular frameworks.',
            'Cloud Computing Concepts' => 'Understand cloud computing models, services, and deployment strategies.',
            'Game Development with Unity' => 'Develop engaging games using Unity, focusing on graphics, physics, and gameplay.',
            'Blockchain Technology' => 'Explore the principles of blockchain and its applications in various industries.',
            'Photography Basics' => 'Learn the fundamentals of photography, including composition, lighting, and editing.',
            'Public Speaking Skills' => 'Enhance your public speaking abilities and learn effective communication techniques.',
            'Financial Literacy for Beginners' => 'Understand personal finance, budgeting, and investment strategies.',
            'Creative Writing Workshop' => 'Develop your writing skills through exercises and feedback on your work.',
        ];
        $instructors = Instructor::all();
        $categories = Category::all();
        foreach ($instructors as $instructor) {
            $numberOfCourses = rand(1, 3);
            for ($i = 1; $i <= $numberOfCourses; $i++) {
                $randomTitle = array_rand($coursesData);
                $categories = $instructor->categories;
                $randomCategories = $categories->random(rand(1, min(2, $categories->count())));
                $course=Course::create([
                    'instructor_id' => $instructor->id,
                    'title' => $randomTitle,
                    'image' => 'default.png',
                    'views' => rand(0,100),
                    'description' => $coursesData[$randomTitle],
                    'price' =>  rand(1, 20).'0',
                    'level' => rand(1, 5) > 2 ? rand(1, 5) : null,
                    'discount' => rand(0, 10) > 5 ? rand(1, 30) : 0,
                    'rating'=> 0,
                ]);
                $randomCategories = $categories->random(rand(1, min(2, $categories->count())));
                $attachIds = [];
                foreach ($randomCategories as $category) {
                    $attachIds[] = $category->id;
                    if ($category->parent_id) {
                        $attachIds[] = $category->parent_id;
                    }
                }
                $course->categories()->attach(array_unique($attachIds));
                $instructor->categories()->syncWithoutDetaching(array_unique($attachIds));
            }
        }
    }
}
