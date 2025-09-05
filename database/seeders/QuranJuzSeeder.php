<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\SubjectDetail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuranJuzSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Subject::where('name', 'LIKE', 'Juz%')->delete();

        $juz1 = Subject::create([
            'name' => 'Juz 1 - الم',
            'description' => 'Juz pertama Al-Quran dimulai dari surat Al-Fatihah hingga Al-Baqarah ayat 141',
            'has_details' => false
        ]);

        $juz2 = Subject::create([
            'name' => 'Juz 2 - سيقول',
            'description' => 'Juz kedua Al-Quran dari Al-Baqarah ayat 142 hingga ayat 252',
            'has_details' => false
        ]);

        $juz3 = Subject::create([
            'name' => 'Juz 3 - تلك الرسل',
            'description' => 'Juz ketiga Al-Quran dari Al-Baqarah ayat 253 hingga Ali Imran ayat 92',
            'has_details' => false
        ]);

        $juz4 = Subject::create([
            'name' => 'Juz 4 - لن تنالوا',
            'description' => 'Juz keempat Al-Quran dari Ali Imran ayat 93 hingga An-Nisa ayat 23',
            'has_details' => false
        ]);

        $juz5 = Subject::create([
            'name' => 'Juz 5 - والمحصنات',
            'description' => 'Juz kelima Al-Quran dari An-Nisa ayat 24 hingga ayat 147',
            'has_details' => false
        ]);

        $juz6 = Subject::create([
            'name' => 'Juz 6 - لا يحب الله',
            'description' => 'Juz keenam Al-Quran dari An-Nisa ayat 148 hingga Al-Maidah ayat 81',
            'has_details' => false
        ]);

        $juz29 = Subject::create([
            'name' => 'Juz 29 - تبارك',
            'description' => 'Juz ke-29 Al-Quran dimulai dari surat Al-Mulk hingga Al-Mursalat',
            'has_details' => true
        ]);

        $juz29Details = [
            ['name' => 'الملك', 'name_latin' => 'Al-Mulk', 'description' => 'Surat Al-Mulk (Kerajaan) - 30 ayat', 'order' => 1],
            ['name' => 'القلم', 'name_latin' => 'Al-Qalam', 'description' => 'Surat Al-Qalam (Pena) - 52 ayat', 'order' => 2],
            ['name' => 'الحاقة', 'name_latin' => 'Al-Haqqah', 'description' => 'Surat Al-Haqqah (Hari Kiamat) - 52 ayat', 'order' => 3],
            ['name' => 'المعارج', 'name_latin' => 'Al-Ma\'arij', 'description' => 'Surat Al-Ma\'arij (Tempat-tempat Naik) - 44 ayat', 'order' => 4],
            ['name' => 'نوح', 'name_latin' => 'Nuh', 'description' => 'Surat Nuh (Nabi Nuh) - 28 ayat', 'order' => 5],
            ['name' => 'الجن', 'name_latin' => 'Al-Jinn', 'description' => 'Surat Al-Jinn (Jin) - 28 ayat', 'order' => 6],
            ['name' => 'المزمل', 'name_latin' => 'Al-Muzzammil', 'description' => 'Surat Al-Muzzammil (Orang yang Berselimut) - 20 ayat', 'order' => 7],
            ['name' => 'المدثر', 'name_latin' => 'Al-Muddaththir', 'description' => 'Surat Al-Muddaththir (Orang yang Berkemul) - 56 ayat', 'order' => 8],
            ['name' => 'القيامة', 'name_latin' => 'Al-Qiyamah', 'description' => 'Surat Al-Qiyamah (Hari Kiamat) - 40 ayat', 'order' => 9],
            ['name' => 'الإنسان', 'name_latin' => 'Al-Insan', 'description' => 'Surat Al-Insan (Manusia) - 31 ayat', 'order' => 10],
            ['name' => 'المرسلات', 'name_latin' => 'Al-Mursalat', 'description' => 'Surat Al-Mursalat (Malaikat-malaikat yang Diutus) - 50 ayat', 'order' => 11],
        ];

        foreach ($juz29Details as $detail) {
            SubjectDetail::create([
                'subject_id' => $juz29->id,
                'name' => $detail['name'] . ' (' . $detail['name_latin'] . ')',
                'description' => $detail['description'],
                'order' => $detail['order'],
            ]);
        }

        $juz30 = Subject::create([
            'name' => 'Juz 30 - عم',
            'description' => 'Juz ke-30 Al-Quran (Juz Amma) dimulai dari surat An-Naba hingga An-Nas',
            'has_details' => true
        ]);

        $juz30Details = [
            ['name' => 'النبأ', 'name_latin' => 'An-Naba\'', 'description' => 'Surat An-Naba\' (Berita Besar) - 40 ayat', 'order' => 1],
            ['name' => 'النازعات', 'name_latin' => 'An-Nazi\'at', 'description' => 'Surat An-Nazi\'at (Malaikat yang Mencabut) - 46 ayat', 'order' => 2],
            ['name' => 'عبس', 'name_latin' => 'Abasa', 'description' => 'Surat Abasa (Dia Bermuka Masam) - 42 ayat', 'order' => 3],
            ['name' => 'التكوير', 'name_latin' => 'At-Takwir', 'description' => 'Surat At-Takwir (Penggulungan) - 29 ayat', 'order' => 4],
            ['name' => 'الانفطار', 'name_latin' => 'Al-Infitar', 'description' => 'Surat Al-Infitar (Terbelah) - 19 ayat', 'order' => 5],
            ['name' => 'المطففين', 'name_latin' => 'Al-Mutaffifin', 'description' => 'Surat Al-Mutaffifin (Orang-orang Curang) - 36 ayat', 'order' => 6],
            ['name' => 'الانشقاق', 'name_latin' => 'Al-Inshiqaq', 'description' => 'Surat Al-Inshiqaq (Terbelah) - 25 ayat', 'order' => 7],
            ['name' => 'البروج', 'name_latin' => 'Al-Buruj', 'description' => 'Surat Al-Buruj (Gugusan Bintang) - 22 ayat', 'order' => 8],
            ['name' => 'الطارق', 'name_latin' => 'At-Tariq', 'description' => 'Surat At-Tariq (Yang Datang di Malam Hari) - 17 ayat', 'order' => 9],
            ['name' => 'الأعلى', 'name_latin' => 'Al-A\'la', 'description' => 'Surat Al-A\'la (Yang Paling Tinggi) - 19 ayat', 'order' => 10],
            ['name' => 'الغاشية', 'name_latin' => 'Al-Ghashiyah', 'description' => 'Surat Al-Ghashiyah (Hari Pembalasan) - 26 ayat', 'order' => 11],
            ['name' => 'الفجر', 'name_latin' => 'Al-Fajr', 'description' => 'Surat Al-Fajr (Fajar) - 30 ayat', 'order' => 12],
            ['name' => 'البلد', 'name_latin' => 'Al-Balad', 'description' => 'Surat Al-Balad (Negeri) - 20 ayat', 'order' => 13],
            ['name' => 'الشمس', 'name_latin' => 'Ash-Shams', 'description' => 'Surat Ash-Shams (Matahari) - 15 ayat', 'order' => 14],
            ['name' => 'الليل', 'name_latin' => 'Al-Lail', 'description' => 'Surat Al-Lail (Malam) - 21 ayat', 'order' => 15],
            ['name' => 'الضحى', 'name_latin' => 'Ad-Duha', 'description' => 'Surat Ad-Duha (Waktu Duha) - 11 ayat', 'order' => 16],
            ['name' => 'الشرح', 'name_latin' => 'Ash-Sharh', 'description' => 'Surat Ash-Sharh (Pelapangan) - 8 ayat', 'order' => 17],
            ['name' => 'التين', 'name_latin' => 'At-Tin', 'description' => 'Surat At-Tin (Buah Tin) - 8 ayat', 'order' => 18],
            ['name' => 'العلق', 'name_latin' => 'Al-\'Alaq', 'description' => 'Surat Al-\'Alaq (Segumpal Darah) - 19 ayat', 'order' => 19],
            ['name' => 'القدر', 'name_latin' => 'Al-Qadr', 'description' => 'Surat Al-Qadr (Kemuliaan) - 5 ayat', 'order' => 20],
            ['name' => 'البينة', 'name_latin' => 'Al-Bayyinah', 'description' => 'Surat Al-Bayyinah (Bukti yang Nyata) - 8 ayat', 'order' => 21],
            ['name' => 'الزلزلة', 'name_latin' => 'Az-Zalzalah', 'description' => 'Surat Az-Zalzalah (Guncangan) - 8 ayat', 'order' => 22],
            ['name' => 'العاديات', 'name_latin' => 'Al-\'Adiyat', 'description' => 'Surat Al-\'Adiyat (Kuda Perang) - 11 ayat', 'order' => 23],
            ['name' => 'القارعة', 'name_latin' => 'Al-Qari\'ah', 'description' => 'Surat Al-Qari\'ah (Hari Kiamat) - 11 ayat', 'order' => 24],
            ['name' => 'التكاثر', 'name_latin' => 'At-Takathur', 'description' => 'Surat At-Takathur (Bermegah-megahan) - 8 ayat', 'order' => 25],
            ['name' => 'العصر', 'name_latin' => 'Al-\'Asr', 'description' => 'Surat Al-\'Asr (Masa) - 3 ayat', 'order' => 26],
            ['name' => 'الهمزة', 'name_latin' => 'Al-Humazah', 'description' => 'Surat Al-Humazah (Pengumpat) - 9 ayat', 'order' => 27],
            ['name' => 'الفيل', 'name_latin' => 'Al-Fil', 'description' => 'Surat Al-Fil (Gajah) - 5 ayat', 'order' => 28],
            ['name' => 'قريش', 'name_latin' => 'Quraish', 'description' => 'Surat Quraish (Suku Quraish) - 4 ayat', 'order' => 29],
            ['name' => 'الماعون', 'name_latin' => 'Al-Ma\'un', 'description' => 'Surat Al-Ma\'un (Barang Berguna) - 7 ayat', 'order' => 30],
            ['name' => 'الكوثر', 'name_latin' => 'Al-Kawthar', 'description' => 'Surat Al-Kawthar (Nikmat yang Berlimpah) - 3 ayat', 'order' => 31],
            ['name' => 'الكافرون', 'name_latin' => 'Al-Kafirun', 'description' => 'Surat Al-Kafirun (Orang-orang Kafir) - 6 ayat', 'order' => 32],
            ['name' => 'النصر', 'name_latin' => 'An-Nasr', 'description' => 'Surat An-Nasr (Pertolongan) - 3 ayat', 'order' => 33],
            ['name' => 'المسد', 'name_latin' => 'Al-Masad', 'description' => 'Surat Al-Masad (Sabut) - 5 ayat', 'order' => 34],
            ['name' => 'الإخلاص', 'name_latin' => 'Al-Ikhlas', 'description' => 'Surat Al-Ikhlas (Kemurnian Tauhid) - 4 ayat', 'order' => 35],
            ['name' => 'الفلق', 'name_latin' => 'Al-Falaq', 'description' => 'Surat Al-Falaq (Waktu Subuh) - 5 ayat', 'order' => 36],
            ['name' => 'الناس', 'name_latin' => 'An-Nas', 'description' => 'Surat An-Nas (Manusia) - 6 ayat', 'order' => 37],
        ];

        foreach ($juz30Details as $detail) {
            SubjectDetail::create([
                'subject_id' => $juz30->id,
                'name' => $detail['name'] . ' (' . $detail['name_latin'] . ')',
                'description' => $detail['description'],
                'order' => $detail['order'],
            ]);
        }
    }
}
