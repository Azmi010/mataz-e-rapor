<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activities = [
            // Aktivitas Sekolah
            ['name' => 'Mengaji Al-Quran', 'activity_type' => 'Sekolah'],
            ['name' => 'Sholat Dhuha Berjamaah', 'activity_type' => 'Sekolah'],
            ['name' => 'Sholat Dzuhur Berjamaah', 'activity_type' => 'Sekolah'],
            ['name' => 'Sholat Ashar Berjamaah', 'activity_type' => 'Sekolah'],
            ['name' => 'Membaca Asmaul Husna', 'activity_type' => 'Sekolah'],
            ['name' => 'Hafalan Surat Pendek', 'activity_type' => 'Sekolah'],
            ['name' => 'Infaq Harian', 'activity_type' => 'Sekolah'],
            ['name' => 'Membersihkan Kelas', 'activity_type' => 'Sekolah'],
            
            // Aktivitas Rumah
            ['name' => 'Sholat 5 Waktu', 'activity_type' => 'Rumah'],
            ['name' => 'Mengaji di Rumah', 'activity_type' => 'Rumah'],
            ['name' => 'Membantu Orang Tua', 'activity_type' => 'Rumah'],
            ['name' => 'Sholat Tahajud', 'activity_type' => 'Rumah'],
            ['name' => 'Puasa Sunnah', 'activity_type' => 'Rumah'],
            ['name' => 'Sedekah', 'activity_type' => 'Rumah'],
            ['name' => 'Silaturahmi', 'activity_type' => 'Rumah'],
        ];

        foreach ($activities as $activity) {
            Activity::create($activity);
        }
    }
}
