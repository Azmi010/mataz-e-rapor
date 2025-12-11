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
                'name' => 'Mapel Agama',
                'description' => 'Mata pelajaran yang berkaitan dengan pendidikan agama Islam'
            ],
            [
                'name' => 'Mapel Umum',
                'description' => 'Mata pelajaran umum'
            ],
            [
                'name' => 'Muatan Lokal',
                'description' => 'Mata pelajaran muatan lokal'
            ],
        ];

        foreach ($categories as $category) {
            SubjectCategory::create($category);
        }
    }
}
