<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tahfidz;

class TahfidzSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tahfidzData = [
            // Juz 30
            ['name' => 'An-Naba\'', 'arabic_name' => 'النبإ', 'juz' => 30],
            ['name' => 'An-Nazi\'at', 'arabic_name' => 'النازعات', 'juz' => 30],
            ['name' => '\'Abasa', 'arabic_name' => 'عبس', 'juz' => 30],
            ['name' => 'At-Takwir', 'arabic_name' => 'التكوير', 'juz' => 30],
            ['name' => 'Al-Infitar', 'arabic_name' => 'الإنفطار', 'juz' => 30],
            ['name' => 'Al-Mutaffifin', 'arabic_name' => 'المطففين', 'juz' => 30],
            ['name' => 'Al-Insyiqaq', 'arabic_name' => 'الإنشقاق', 'juz' => 30],
            ['name' => 'Al-Buruj', 'arabic_name' => 'البروج', 'juz' => 30],
            ['name' => 'At-Tariq', 'arabic_name' => 'الطارق', 'juz' => 30],
            ['name' => 'Al-A\'la', 'arabic_name' => 'الأعلى', 'juz' => 30],
            ['name' => 'Al-Ghasyiyah', 'arabic_name' => 'الغاشية', 'juz' => 30],
            ['name' => 'Al-Fajr', 'arabic_name' => 'الفجر', 'juz' => 30],
            ['name' => 'Al-Balad', 'arabic_name' => 'البلد', 'juz' => 30],
            ['name' => 'Asy-Syams', 'arabic_name' => 'الشمس', 'juz' => 30],
            ['name' => 'Al-Lail', 'arabic_name' => 'الليل', 'juz' => 30],
            ['name' => 'Ad-Duha', 'arabic_name' => 'الضحى', 'juz' => 30],
            ['name' => 'Asy-Syarh', 'arabic_name' => 'الشرح', 'juz' => 30],
            ['name' => 'At-Tin', 'arabic_name' => 'التين', 'juz' => 30],
            ['name' => 'Al-\'Alaq', 'arabic_name' => 'العلق', 'juz' => 30],
            ['name' => 'Al-Qadr', 'arabic_name' => 'القدر', 'juz' => 30],
            ['name' => 'Al-Bayyinah', 'arabic_name' => 'البينة', 'juz' => 30],
            ['name' => 'Az-Zalzalah', 'arabic_name' => 'الزلزلة', 'juz' => 30],
            ['name' => 'Al-\'Adiyat', 'arabic_name' => 'العاديات', 'juz' => 30],
            ['name' => 'Al-Qari\'ah', 'arabic_name' => 'القارعة', 'juz' => 30],
            ['name' => 'At-Takatsur', 'arabic_name' => 'التكاثر', 'juz' => 30],
            ['name' => 'Al-\'Asr', 'arabic_name' => 'العصر', 'juz' => 30],
            ['name' => 'Al-Humazah', 'arabic_name' => 'الهمزة', 'juz' => 30],
            ['name' => 'Al-Fil', 'arabic_name' => 'الفيل', 'juz' => 30],
            ['name' => 'Quraisy', 'arabic_name' => 'قريش', 'juz' => 30],
            ['name' => 'Al-Ma\'un', 'arabic_name' => 'الماعون', 'juz' => 30],
            ['name' => 'Al-Kautsar', 'arabic_name' => 'الكوثر', 'juz' => 30],
            ['name' => 'Al-Kafirun', 'arabic_name' => 'الكافرون', 'juz' => 30],
            ['name' => 'An-Nasr', 'arabic_name' => 'النصر', 'juz' => 30],
            ['name' => 'Al-Lahab', 'arabic_name' => 'المسد', 'juz' => 30],
            ['name' => 'Al-Ikhlas', 'arabic_name' => 'الإخلاص', 'juz' => 30],
            ['name' => 'Al-Falaq', 'arabic_name' => 'الفلق', 'juz' => 30],
            ['name' => 'An-Nas', 'arabic_name' => 'الناس', 'juz' => 30],

            // Juz 29
            ['name' => 'Al-Mulk', 'arabic_name' => 'الملك', 'juz' => 29],
            ['name' => 'Al-Qalam', 'arabic_name' => 'القلم', 'juz' => 29],
            ['name' => 'Al-Haqqah', 'arabic_name' => 'الحاقة', 'juz' => 29],
            ['name' => 'Al-Ma\'arij', 'arabic_name' => 'المعارج', 'juz' => 29],
            ['name' => 'Nuh', 'arabic_name' => 'نوح', 'juz' => 29],
            ['name' => 'Al-Jinn', 'arabic_name' => 'الجن', 'juz' => 29],
            ['name' => 'Al-Muzzammil', 'arabic_name' => 'المزمل', 'juz' => 29],
            ['name' => 'Al-Muddatstsir', 'arabic_name' => 'المدثر', 'juz' => 29],
            ['name' => 'Al-Qiyamah', 'arabic_name' => 'القيامة', 'juz' => 29],
            ['name' => 'Al-Insan', 'arabic_name' => 'الإنسان', 'juz' => 29],
            ['name' => 'Al-Mursalat', 'arabic_name' => 'المرسلات', 'juz' => 29],
        ];

        foreach ($tahfidzData as $data) {
            Tahfidz::create($data);
        }
    }
}
