<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([
            UsersTableSeeder::class,
            InstructorsTableSeeder::class,
            StudentsTableSeeder::class,
            CategoriesTableSeeder::class,
            CoursesTableSeeder::class,
            PivotTablesSeeder::class,
            EnrollmentsSeeder::class,
            CouponsTableSeeder::class,
            CommentsTableSeeder::class,
        ]);
    }
}
