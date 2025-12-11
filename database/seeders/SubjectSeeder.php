<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\SubjectCategory;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Mapel Agama' => [
                'Al-Qur\'an Hadits',
                'Aqidah Akhlak',
                'Fiqih',
                'Sejarah Kebudayaan Islam',
                'Bahasa Arab',
            ],
            'Mapel Umum' => [
                'Pendidikan Pancasila',
                'Bahasa Indonesia',
                'Matematika',
                'Ilmu Pengetahuan Alam dan Sosial',
                'Seni Budaya dan Prakarya',
                'Pendidikan Jasmani, Olahraga dan Kesehatan',
                'Coding',
            ],
            'Muatan Lokal' => [
                'Bahasa Inggris',
                'Nahwu',
                'Shorof',
                'Tahfidz',
                'Tahshin',
                'Tauhid',
            ],
        ];

        foreach ($data as $categoryName => $subjects) {

            $category = SubjectCategory::where('name', $categoryName)->first();

            if ($category) {
                foreach ($subjects as $subjectName) {
                    Subject::create([
                        'category_id' => $category->id,
                        'name' => $subjectName,
                        'description' => 'Mata pelajaran ' . $subjectName,
                    ]);
                }
            }
        }
    }
}
