<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Instructor;
use App\Models\User;

class InstructorsTableSeeder extends Seeder
{
    public function run()
    {
        $userIds = User::inRandomOrder()->limit(20)->pluck('id');

        foreach ($userIds as $id) {
            $user = User::find($id);

            Instructor::create([
                'user_id' => $id,
                'verified' => true,
                'full_name' => $user->first_name . ' ' . $user->last_name,
                'views' => rand(100, 5000),
                'bio' => 'Professional instructor specialized in ' . fake()->randomElement([
                        'programming', 'engineering', 'design', 'business', 'education'
                    ]),
                'rating' => rand(3, 5),
                'cv_path' => null,
                'enabled' => true,
                'current_balance' => rand(100, 500),
                'total_balance' => rand(1000, 5000),
            ]);
        }
    }
}
