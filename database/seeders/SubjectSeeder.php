<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            ['name' => 'Matematika', 'has_details' => true],
            ['name' => 'Bahasa Indonesia', 'has_details' => true],
            ['name' => 'Bahasa Inggris', 'has_details' => true],
            ['name' => 'Fisika', 'has_details' => true],
            ['name' => 'Kimia', 'has_details' => true],
            ['name' => 'Biologi', 'has_details' => true],
            ['name' => 'Sejarah', 'has_details' => false],
            ['name' => 'Geografi', 'has_details' => false],
            ['name' => 'Ekonomi', 'has_details' => true],
            ['name' => 'Sosiologi', 'has_details' => false],
            ['name' => 'Pendidikan Agama Islam', 'has_details' => false],
            ['name' => 'Pendidikan Kewarganegaraan', 'has_details' => false],
            ['name' => 'Seni Budaya', 'has_details' => false],
            ['name' => 'Pendidikan Jasmani', 'has_details' => false],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
}
