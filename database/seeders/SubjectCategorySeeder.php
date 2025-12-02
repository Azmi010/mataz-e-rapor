<?php

namespace Database\Seeders;

use App\Models\SubjectCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubjectCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Pendidikan Agama Islam',
                'description' => 'Mata pelajaran yang berkaitan dengan pendidikan agama Islam'
            ],
            [
                'name' => 'Tahfidz Al-Quran',
                'description' => 'Mata pelajaran tahfidz dan hafalan Al-Quran'
            ],
            [
                'name' => 'Bahasa',
                'description' => 'Mata pelajaran bahasa Indonesia, Arab, dan Inggris'
            ],
            [
                'name' => 'Matematika',
                'description' => 'Mata pelajaran matematika dan ilmu hitung'
            ],
            [
                'name' => 'Ilmu Pengetahuan Alam',
                'description' => 'Mata pelajaran IPA, Biologi, Fisika, Kimia'
            ],
            [
                'name' => 'Ilmu Pengetahuan Sosial',
                'description' => 'Mata pelajaran IPS, Sejarah, Geografi'
            ],
            [
                'name' => 'Seni dan Budaya',
                'description' => 'Mata pelajaran seni, musik, dan budaya'
            ],
            [
                'name' => 'Pendidikan Jasmani',
                'description' => 'Mata pelajaran olahraga dan kesehatan'
            ],
            [
                'name' => 'Muatan Lokal',
                'description' => 'Mata pelajaran muatan lokal dan keterampilan'
            ],
        ];

        foreach ($categories as $category) {
            SubjectCategory::create($category);
        }
    }
}
