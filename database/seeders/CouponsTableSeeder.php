<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Coupon;
use Illuminate\Support\Str;

class CouponsTableSeeder extends Seeder
{
    public function run()
    {
        $courses = Course::take(5)->get();

        foreach ($courses as $course) {
            for ($i = 1; $i <= 3; $i++) {
                Coupon::create([
                    'instructor_id' => $course->instructor_id,
                    'code'          => strtoupper(Str::random(8)),
                    'value'         => rand(10, 50), // خصم بين 10 و 50
                    'expires_at'    => now()->addDays(rand(15, 90)), // صلاحية 15 - 90 يوم
                    'is_active'     => true,
                ]);
            }
        }
    }
}
