<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat beberapa user guru
        $teacherUsers = [
            [
                'name' => 'Prof. Siti Aminah',
                'email' => 'siti.aminah@teacher.mataz.sch.id',
                'password' => Hash::make('password'),
                'role' => 'teacher',
            ],
            [
                'name' => 'Dr. Ahmad Fauzi',
                'email' => 'ahmad.fauzi@teacher.mataz.sch.id',
                'password' => Hash::make('password'),
                'role' => 'teacher',
            ],
            [
                'name' => 'Ustadzah Fatimah',
                'email' => 'fatimah@teacher.mataz.sch.id',
                'password' => Hash::make('password'),
                'role' => 'teacher',
            ],
        ];

        foreach ($teacherUsers as $userData) {
            $user = User::create($userData);

            // Buat teacher record
            Teacher::create([
                'user_id' => $user->id,
                'nip' => 'NIP' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
            ]);
        }
    }
}
