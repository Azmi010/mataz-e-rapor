<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Teacher; // Pastikan import model Teacher
use App\Models\Student; // Pastikan import model Student
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin MATAZ',
            'email' => 'admin@mataz.sch.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $teacherUser = User::create([
            'name' => 'Guru Pengajar',
            'email' => 'guru@mataz.sch.id',
            'password' => Hash::make('password'),
            'role' => 'teacher',
        ]);

        Teacher::create([
            'user_id' => $teacherUser->id,
            'nip' => '198501012010011001',
            'phone' => '081234567890',
            'address' => 'Jl. Pendidikan No. 1, Lamongan',
        ]);

        $studentUser = User::create([
            'name' => 'Siswa Teladan',
            'email' => 'siswa@mataz.sch.id',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);

        Student::create([
            'user_id' => $studentUser->id,
            'nis' => '2024001',
            'gender' => 'L',
            'class_id' => null,
            'phone' => '089876543210',
            'address' => 'Jl. Pelajar No. 5, Lamongan',
        ]);
    }
}
