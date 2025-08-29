<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\User;

class StudentsTableSeeder extends Seeder
{
    public function run()
    {
        $userIds = User::inRandomOrder()->limit(20)->pluck('id');

        foreach ($userIds as $id) {
            $user = User::find($id);

            Student::create([
                'user_id' => $id,
                'full_name' => $user->first_name . ' ' . $user->last_name,
            ]);
        }
    }
}
