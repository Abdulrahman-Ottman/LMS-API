<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $users = [
            ['Ahmed', 'Khaled', 'ahmedkhaled', 'ahmedkhaled@example.com'],
            ['Omar', 'Youssef', 'omaryoussef', 'omaryoussef@example.com'],
            ['Mohamed', 'Ali', 'mohamedali', 'mohamedali@example.com'],
            ['Hassan', 'Ibrahim', 'hassani', 'hassani@example.com'],
            ['Yousef', 'Mahmoud', 'yousefm', 'yousefm@example.com'],
            ['Khaled', 'Sami', 'khaleds', 'khaleds@example.com'],
            ['Tamer', 'Hussein', 'tamerh', 'tamerh@example.com'],
            ['Mona', 'Ahmed', 'monaa', 'monaa@example.com'],
            ['Sara', 'Fouad', 'saraf', 'saraf@example.com'],
            ['Laila', 'Hassan', 'lailah', 'lailah@example.com'],
            ['Nour', 'Kamel', 'nourk', 'nourk@example.com'],
            ['Aya', 'Othman', 'ayao', 'ayao@example.com'],
            ['Salma', 'Mostafa', 'salmam', 'salmam@example.com'],
            ['Huda', 'Adel', 'hudaa', 'hudaa@example.com'],
            ['Rania', 'Kareem', 'raniak', 'raniak@example.com'],
            ['Farah', 'Omar', 'farao', 'farao@example.com'],
            ['Ola', 'Saeed', 'olaa', 'olaa@example.com'],
            ['Mai', 'Hany', 'maih', 'maih@example.com'],
            ['Reem', 'Zaki', 'reemz', 'reemz@example.com'],
            ['Heba', 'Ashraf', 'hebaa', 'hebaa@example.com'],
            ['Karim', 'Lotfi', 'kariml', 'kariml@example.com'],
            ['Amr', 'Samir', 'amrs', 'amrs@example.com'],
            ['Mostafa', 'Tarek', 'mostafat', 'mostafat@example.com'],
            ['Ali', 'Sayed', 'alis', 'alis@example.com'],
            ['Walid', 'Nabil', 'walidn', 'walidn@example.com'],
            ['Adel', 'Shawky', 'adels', 'adels@example.com'],
            ['Ibrahim', 'Hamed', 'ibrahimh', 'ibrahimh@example.com'],
            ['Ehab', 'Saad', 'ehabs', 'ehabs@example.com'],
            ['Sherif', 'Magdy', 'sherifm', 'sherifm@example.com'],
            ['Mahmoud', 'Lotfy', 'mahmoudl', 'mahmoudl@example.com'],
            ['Samir', 'Hany', 'samirh', 'samirh@example.com'],
            ['Tarek', 'Zein', 'tarekm', 'tarekm@example.com'],
            ['Bassel', 'Sami', 'bassels', 'bassels@example.com'],
            ['Othman', 'Adel', 'othmana', 'othmana@example.com'],
            ['Hany', 'Selim', 'hanys', 'hanys@example.com'],
            ['Sherine', 'Hassan', 'sherineh', 'sherineh@example.com'],
            ['Nadia', 'Lotfi', 'nadial', 'nadial@example.com'],
            ['Hind', 'Fathi', 'hindf', 'hindf@example.com'],
            ['Dina', 'Aly', 'dinaa', 'dinaa@example.com'],
        ];

        foreach ($users as $index => $u) {
            User::create([
                'first_name' => $u[0],
                'last_name' => $u[1],
                'user_name' => $u[2],
                'email' => $u[3],
                'password' => Hash::make('password'),
                'avatar' => 'storage/public/images/avatars/default.png',
                'role' => $index < 20 ? 'student' : 'instructor', // أول 20 طلاب - الباقي مدرّسين
            ]);
        }
    }
}
