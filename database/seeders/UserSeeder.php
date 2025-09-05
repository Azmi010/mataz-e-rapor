<?php

namespace Database\Seeders;

use App\Models\User;
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

        User::create([
            'name' => 'Guru Pengajar',
            'email' => 'guru@mataz.sch.id',
            'password' => Hash::make('password'),
            'role' => 'teacher',
        ]);

        User::create([
            'name' => 'Siswa Teladan',
            'email' => 'siswa@mataz.sch.id',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);
    }
}
