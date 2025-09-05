<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat beberapa user siswa
        $studentUsers = [
            [
                'name' => 'Ahmad Rizki',
                'email' => 'ahmad.rizki@student.mataz.sch.id',
                'password' => Hash::make('password'),
                'role' => 'student',
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@student.mataz.sch.id',
                'password' => Hash::make('password'),
                'role' => 'student',
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@student.mataz.sch.id',
                'password' => Hash::make('password'),
                'role' => 'student',
            ],
            [
                'name' => 'Dewi Kartika',
                'email' => 'dewi.kartika@student.mataz.sch.id',
                'password' => Hash::make('password'),
                'role' => 'student',
            ],
            [
                'name' => 'Eko Prasetyo',
                'email' => 'eko.prasetyo@student.mataz.sch.id',
                'password' => Hash::make('password'),
                'role' => 'student',
            ],
        ];

        foreach ($studentUsers as $userData) {
            $user = User::create($userData);

            // Buat student record
            Student::create([
                'user_id' => $user->id,
                'nis' => '2024' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                'wali' => 'Wali ' . $user->name,
                'class_id' => null, // Akan diassign ke kelas nanti
            ]);
        }
    }
}
