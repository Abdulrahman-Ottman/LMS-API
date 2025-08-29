<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesTableSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Programming' => ['Python', 'Java', 'C++', 'PHP', 'JavaScript'],
            'Web Development' => ['Frontend', 'Backend', 'Fullstack', 'HTML & CSS', 'Web Frameworks'],
            'Mobile Development' => ['Android', 'iOS', 'Flutter', 'React Native', 'Xamarin'],
            'Software Engineering' => ['Agile Methods', 'Design Patterns', 'UML', 'Software Testing', 'DevOps'],
            'Artificial Intelligence' => ['Neural Networks', 'NLP', 'Computer Vision', 'Reinforcement Learning', 'AI Ethics'],
            'Data Science' => ['Data Analysis', 'Data Visualization', 'Big Data', 'Statistics', 'Data Mining'],
            'Machine Learning' => ['Supervised Learning', 'Unsupervised Learning', 'Deep Learning', 'Time Series', 'Model Deployment'],
            'Cyber Security' => ['Ethical Hacking', 'Cryptography', 'Network Security', 'Application Security', 'Forensics'],
            'Networks' => ['Network Basics', 'Routing & Switching', 'Wireless Networks', 'Network Protocols', 'IoT Networks'],
            'Cloud Computing' => ['AWS', 'Azure', 'Google Cloud', 'Cloud Security', 'Serverless'],
            'Operating Systems' => ['Linux', 'Windows', 'MacOS', 'OS Internals', 'Embedded OS'],
            'Database Systems' => ['SQL', 'NoSQL', 'PostgreSQL', 'MongoDB', 'Database Design'],
            'Computer Graphics' => ['2D Graphics', '3D Graphics', 'Game Engines', 'Rendering', 'Animation'],
            'Project Management' => ['Scrum', 'Kanban', 'Risk Management', 'Planning Tools', 'Leadership'],
            'Business Analysis' => ['Requirement Gathering', 'Process Modeling', 'SWOT Analysis', 'KPIs', 'Market Research'],
        ];

        foreach ($categories as $main => $subs) {
            // إنشاء الفئة الرئيسية
            $mainCategory = Category::create([
                'name' => $main,
                'parent_id' => null,
                'image' => 'storage/public/images/categories/default.png',
            ]);

            // إنشاء الفئات الفرعية
            foreach ($subs as $sub) {
                Category::create([
                    'name' => $sub,
                    'parent_id' => $mainCategory->id,
                    'image' => 'storage/public/images/categories/default.png',
                ]);
            }
        }
    }
}
