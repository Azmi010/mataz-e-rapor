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
            ['name' => 'An-Naba\'', 'arabic_name' => 'النبإ', 'juz' => 30, 'ayat_range' => '1-40', 'order' => 1],
            ['name' => 'An-Nazi\'at', 'arabic_name' => 'النازعات', 'juz' => 30, 'ayat_range' => '1-46', 'order' => 2],
            ['name' => '\'Abasa', 'arabic_name' => 'عبس', 'juz' => 30, 'ayat_range' => '1-42', 'order' => 3],
            ['name' => 'At-Takwir', 'arabic_name' => 'التكوير', 'juz' => 30, 'ayat_range' => '1-29', 'order' => 4],
            ['name' => 'Al-Infitar', 'arabic_name' => 'الإنفطار', 'juz' => 30, 'ayat_range' => '1-19', 'order' => 5],
            ['name' => 'Al-Mutaffifin', 'arabic_name' => 'المطففين', 'juz' => 30, 'ayat_range' => '1-36', 'order' => 6],
            ['name' => 'Al-Insyiqaq', 'arabic_name' => 'الإنشقاق', 'juz' => 30, 'ayat_range' => '1-25', 'order' => 7],
            ['name' => 'Al-Buruj', 'arabic_name' => 'البروج', 'juz' => 30, 'ayat_range' => '1-22', 'order' => 8],
            ['name' => 'At-Tariq', 'arabic_name' => 'الطارق', 'juz' => 30, 'ayat_range' => '1-17', 'order' => 9],
            ['name' => 'Al-A\'la', 'arabic_name' => 'الأعلى', 'juz' => 30, 'ayat_range' => '1-19', 'order' => 10],
            ['name' => 'Al-Ghasyiyah', 'arabic_name' => 'الغاشية', 'juz' => 30, 'ayat_range' => '1-26', 'order' => 11],
            ['name' => 'Al-Fajr', 'arabic_name' => 'الفجر', 'juz' => 30, 'ayat_range' => '1-30', 'order' => 12],
            ['name' => 'Al-Balad', 'arabic_name' => 'البلد', 'juz' => 30, 'ayat_range' => '1-20', 'order' => 13],
            ['name' => 'Asy-Syams', 'arabic_name' => 'الشمس', 'juz' => 30, 'ayat_range' => '1-15', 'order' => 14],
            ['name' => 'Al-Lail', 'arabic_name' => 'الليل', 'juz' => 30, 'ayat_range' => '1-21', 'order' => 15],
            ['name' => 'Ad-Duha', 'arabic_name' => 'الضحى', 'juz' => 30, 'ayat_range' => '1-11', 'order' => 16],
            ['name' => 'Asy-Syarh', 'arabic_name' => 'الشرح', 'juz' => 30, 'ayat_range' => '1-8', 'order' => 17],
            ['name' => 'At-Tin', 'arabic_name' => 'التين', 'juz' => 30, 'ayat_range' => '1-8', 'order' => 18],
            ['name' => 'Al-\'Alaq', 'arabic_name' => 'العلق', 'juz' => 30, 'ayat_range' => '1-19', 'order' => 19],
            ['name' => 'Al-Qadr', 'arabic_name' => 'القدر', 'juz' => 30, 'ayat_range' => '1-5', 'order' => 20],
            ['name' => 'Al-Bayyinah', 'arabic_name' => 'البينة', 'juz' => 30, 'ayat_range' => '1-8', 'order' => 21],
            ['name' => 'Az-Zalzalah', 'arabic_name' => 'الزلزلة', 'juz' => 30, 'ayat_range' => '1-8', 'order' => 22],
            ['name' => 'Al-\'Adiyat', 'arabic_name' => 'العاديات', 'juz' => 30, 'ayat_range' => '1-11', 'order' => 23],
            ['name' => 'Al-Qari\'ah', 'arabic_name' => 'القارعة', 'juz' => 30, 'ayat_range' => '1-11', 'order' => 24],
            ['name' => 'At-Takatsur', 'arabic_name' => 'التكاثر', 'juz' => 30, 'ayat_range' => '1-8', 'order' => 25],
            ['name' => 'Al-\'Asr', 'arabic_name' => 'العصر', 'juz' => 30, 'ayat_range' => '1-3', 'order' => 26],
            ['name' => 'Al-Humazah', 'arabic_name' => 'الهمزة', 'juz' => 30, 'ayat_range' => '1-9', 'order' => 27],
            ['name' => 'Al-Fil', 'arabic_name' => 'الفيل', 'juz' => 30, 'ayat_range' => '1-5', 'order' => 28],
            ['name' => 'Quraisy', 'arabic_name' => 'قريش', 'juz' => 30, 'ayat_range' => '1-4', 'order' => 29],
            ['name' => 'Al-Ma\'un', 'arabic_name' => 'الماعون', 'juz' => 30, 'ayat_range' => '1-7', 'order' => 30],
            ['name' => 'Al-Kautsar', 'arabic_name' => 'الكوثر', 'juz' => 30, 'ayat_range' => '1-3', 'order' => 31],
            ['name' => 'Al-Kafirun', 'arabic_name' => 'الكافرون', 'juz' => 30, 'ayat_range' => '1-6', 'order' => 32],
            ['name' => 'An-Nasr', 'arabic_name' => 'النصر', 'juz' => 30, 'ayat_range' => '1-3', 'order' => 33],
            ['name' => 'Al-Lahab', 'arabic_name' => 'المسد', 'juz' => 30, 'ayat_range' => '1-5', 'order' => 34],
            ['name' => 'Al-Ikhlas', 'arabic_name' => 'الإخلاص', 'juz' => 30, 'ayat_range' => '1-4', 'order' => 35],
            ['name' => 'Al-Falaq', 'arabic_name' => 'الفلق', 'juz' => 30, 'ayat_range' => '1-5', 'order' => 36],
            ['name' => 'An-Nas', 'arabic_name' => 'الناس', 'juz' => 30, 'ayat_range' => '1-6', 'order' => 37],

            // Juz 29
            ['name' => 'Al-Mulk', 'arabic_name' => 'الملك', 'juz' => 29, 'ayat_range' => '1-30', 'order' => 38],
            ['name' => 'Al-Qalam', 'arabic_name' => 'القلم', 'juz' => 29, 'ayat_range' => '1-52', 'order' => 39],
            ['name' => 'Al-Haqqah', 'arabic_name' => 'الحاقة', 'juz' => 29, 'ayat_range' => '1-52', 'order' => 40],
            ['name' => 'Al-Ma\'arij', 'arabic_name' => 'المعارج', 'juz' => 29, 'ayat_range' => '1-44', 'order' => 41],
            ['name' => 'Nuh', 'arabic_name' => 'نوح', 'juz' => 29, 'ayat_range' => '1-28', 'order' => 42],
            ['name' => 'Al-Jinn', 'arabic_name' => 'الجن', 'juz' => 29, 'ayat_range' => '1-28', 'order' => 43],
            ['name' => 'Al-Muzzammil', 'arabic_name' => 'المزمل', 'juz' => 29, 'ayat_range' => '1-20', 'order' => 44],
            ['name' => 'Al-Muddatstsir', 'arabic_name' => 'المدثر', 'juz' => 29, 'ayat_range' => '1-56', 'order' => 45],
            ['name' => 'Al-Qiyamah', 'arabic_name' => 'القيامة', 'juz' => 29, 'ayat_range' => '1-40', 'order' => 46],
            ['name' => 'Al-Insan', 'arabic_name' => 'الإنسان', 'juz' => 29, 'ayat_range' => '1-31', 'order' => 47],
            ['name' => 'Al-Mursalat', 'arabic_name' => 'المرسلات', 'juz' => 29, 'ayat_range' => '1-50', 'order' => 48],
        ];

        foreach ($tahfidzData as $data) {
            Tahfidz::create($data);
        }
    }
}
