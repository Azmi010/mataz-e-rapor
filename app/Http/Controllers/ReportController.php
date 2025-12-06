<?php

namespace App\Http\Controllers;

use App\Models\ReportCard;
use App\Models\Semester;
use App\Models\Student;
use Exception;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private function formatIndonesianDate($date)
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        return $date->format('d') . ' ' . $months[(int)$date->format('m')] . ' ' . $date->format('Y');
    }

    public function generateReport($studentId)
    {
        $student = Student::with(['classModel.homeroomTeacher.user'])->findOrFail($studentId);
        $currentDate = now()->format('Y-m-d');
        $currentSemester = Semester::whereHas('academicYear', function ($q) {
            $q->where('status', true);
        })
            ->whereDate('start_date', '<=', $currentDate)
            ->whereDate('end_date', '>=', $currentDate)
            ->first();

        if (!$currentSemester) {
            $currentSemester = Semester::whereHas('academicYear', function ($q) {
                $q->where('status', true);
            })
                ->orderBy('semester_type', 'asc')
                ->first();
        }

        $reportCard = $student
            ->reportCards()
            ->with(['semester.academicYear', 'grades.subject.category', 'grades.subjectDetail'])
            ->whereHas('semester', function ($q) {
                $q->whereHas('academicYear', fn($aq) => $aq->where('status', true));
            })
            ->latest()
            ->first();

        if (!$reportCard && $currentSemester) {
            $reportCard = new ReportCard([
                'student_id' => $student->id,
                'semester_id' => $currentSemester->id,
                'teacher_comment' => '',
                'attendance' => '{}',
            ]);
            $reportCard->semester = $currentSemester;
            $reportCard->grades = collect();
        }

        $attendanceStats = [];

        if ($reportCard && $reportCard->semester) {
            $attendances = $student
                ->attendances()
                ->where('semester_id', $reportCard->semester->id)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            if (empty($attendances)) {
                $activeSemester = \App\Models\Semester::whereHas(
                    'academicYear',
                    fn($q) => $q->where('status', true),
                )->first();
                if ($activeSemester && $activeSemester->id != $reportCard->semester->id) {
                    $attendances = $student
                        ->attendances()
                        ->where('semester_id', $activeSemester->id)
                        ->selectRaw('status, COUNT(*) as count')
                        ->groupBy('status')
                        ->pluck('count', 'status')
                        ->toArray();
                }

                if (empty($attendances)) {
                    $attendances = $student
                        ->attendances()
                        ->selectRaw('status, COUNT(*) as count')
                        ->groupBy('status')
                        ->pluck('count', 'status')
                        ->toArray();
                }
            }

            $attendanceStats = [
                'Hadir' => $attendances['Hadir'] ?? 0,
                'Sakit' => $attendances['Sakit'] ?? 0,
                'Izin' => $attendances['Izin'] ?? 0,
                'Alpha' => $attendances['Alpha'] ?? 0,
            ];
        } else {
            $attendanceStats = $reportCard?->attendance_array ?? [];
        }

        $totalGrades = $reportCard?->grades->count() ?? 0;
        $totalScore = $reportCard?->grades->sum('grade') ?? 0;
        $averageScore = $totalGrades > 0 ? round($totalScore / $totalGrades, 1) : 0;

        $sakitCount = $attendanceStats['Sakit'] ?? 0;
        $izinCount = $attendanceStats['Izin'] ?? 0;
        $alphaCount = $attendanceStats['Alpha'] ?? 0;
        $totalAbsent = $sakitCount + $izinCount + $alphaCount;

        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        try {
            // Tambahkan font Algerian
            \TCPDF_FONTS::addTTFfont(public_path('fonts/Algerian.ttf'), 'TrueTypeUnicode', '', 96);

            // Tambahkan font Comic Sans MS
            \TCPDF_FONTS::addTTFfont(public_path('fonts/ComicSansMS.ttf'), 'TrueTypeUnicode', '', 96);
        } catch (Exception $e) {
            // Fallback jika font tidak ada
            error_log('Font loading failed: ' . $e->getMessage());
        }

        $pdf->SetCreator('MATAZ El Zahro');
        $pdf->SetTitle('Rapor ' . $student->user->name);

        $pdf->SetMargins(15, 15, 15);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(true, 15);

        $pdf->AddPage();

        // KOP SURAT - Logo kiri dan kanan
        $pdf->Image(public_path('img/logo.png'), 15, 10, 25, 25);
        $pdf->Image(public_path('img/logo.png'), 170, 10, 25, 25);

        // Judul Kop
        $pdf->SetFont('algerian', 'B', 20);
        $pdf->SetY(12);
        $pdf->Cell(0, 5, 'YAYASAN BAITUL HIKMAH ISLAMI', 0, 1, 'C');
        $pdf->SetFont('comicsansms', 'B', 16);
        $pdf->Cell(0, 6, 'MI Tahfidz El-Zahro', 0, 1, 'C');
        $pdf->SetFont('times', '', 11);
        $pdf->Cell(0, 4, 'Dsn. Poncol, Ds. Banjarejo, Kec. Karangbinangun, Kab. Lamongan 62251', 0, 1, 'C');
        $pdf->SetFont('times', '', 11);
        $pdf->Cell(0, 4, 'SK MENKUMHAM RI: AHU-0021017.AH.01.04.Tahun 2021 TANGGAL 30 Agustus 2021', 0, 1, 'C');
        $pdf->Cell(0, 4, 'No. HP 081330578575/081332222366 Email: officialmarkaztahfidzelzahro@gmail.com', 0, 1, 'C');

        // Garis pemisah
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(15, 44, 195, 44);
        $pdf->SetLineWidth(0.2);
        $pdf->Line(15, 44.5, 195, 44.5);

        $pdf->Ln(4);

        // Data Siswa
        $pdf->SetFont('times', '', 11);
        $leftCol = 15;
        $rightCol = 110;
        $y = $pdf->GetY();

        // Kolom Kiri
        $pdf->SetXY($leftCol, $y);
        $pdf->Cell(30, 5, 'Nama', 0, 0);
        $pdf->Cell(3, 5, ':', 0, 0);
        $pdf->Cell(0, 5, $student->user->name ?? '-', 0, 1);

        $pdf->SetX($leftCol);
        $pdf->Cell(30, 5, 'NIS/NISN', 0, 0);
        $pdf->Cell(3, 5, ':', 0, 0);
        $pdf->Cell(0, 5, $student->nis ?? '-', 0, 1);

        $pdf->SetX($leftCol);
        $pdf->Cell(30, 5, 'Madrasah', 0, 0);
        $pdf->Cell(3, 5, ':', 0, 0);
        $pdf->Cell(0, 5, 'MI Tahfidz El-Zahro', 0, 1);

        $pdf->SetX($leftCol);
        $pdf->Cell(30, 5, 'Alamat', 0, 0);
        $pdf->Cell(3, 5, ':', 0, 0);
        $pdf->Cell(0, 5, $student->address ?? '-', 0, 1);

        // Kolom Kanan
        $pdf->SetXY($rightCol, $y);
        $pdf->Cell(30, 5, 'Kelas', 0, 0);
        $pdf->Cell(3, 5, ':', 0, 0);
        $pdf->Cell(0, 5, $student->classModel->name ?? '-', 0, 1);

        $pdf->SetXY($rightCol, $y + 5);
        $pdf->Cell(30, 5, 'Fase', 0, 0);
        $pdf->Cell(3, 5, ':', 0, 0);
        $pdf->Cell(0, 5, '-', 0, 1);

        $pdf->SetXY($rightCol, $y + 10);
        $pdf->Cell(30, 5, 'Semester', 0, 0);
        $pdf->Cell(3, 5, ':', 0, 0);
        $semesterType = $reportCard && $reportCard->semester
            ? ($reportCard->semester->semester_type == 'Ganjil' ? 'Ganjil' : 'Genap')
            : '-';
        $pdf->Cell(0, 5, $semesterType, 0, 1);

        $pdf->SetXY($rightCol, $y + 15);
        $pdf->Cell(30, 5, 'Tahun Pelajaran', 0, 0);
        $pdf->Cell(3, 5, ':', 0, 0);
        $academicYearName = $reportCard && $reportCard->semester && $reportCard->semester->academicYear
            ? $reportCard->semester->academicYear->name
            : '-';
        $pdf->Cell(0, 5, $academicYearName, 0, 1);

        // Garis pemisah
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(15, 67, 195, 67);
        $pdf->SetLineWidth(0.2);
        $pdf->Line(15, 67.5, 195, 67.5);

        $pdf->Ln(7);

        // Judul Rapor
        $pdf->SetFont('times', 'B', 13);
        $pdf->Cell(0, 6, 'CAPAIAN HASIL BELAJAR', 0, 1, 'C');

        $pdf->Ln(5);

        // HALAMAN PERTAMA: RAPOR MATA PELAJARAN (NON-TAHFIDZ)
        $y_start = $pdf->GetY();

        if ($reportCard && $reportCard->grades->count() > 0) {
            // Kelompokkan semua nilai berdasarkan kategori
            // Hanya ambil grade yang subject_detail_id = null (main grade)
            $groupedByCategory = collect();
            foreach ($reportCard->grades as $grade) {
                if ($grade->subject && $grade->subject_detail_id == null) {
                    $categoryId = $grade->subject->category_id ?? 'uncategorized';
                    if (!$groupedByCategory->has($categoryId)) {
                        $groupedByCategory->put($categoryId, collect());
                    }
                    $groupedByCategory->get($categoryId)->push($grade);
                }
            }

            // Header tabel
            $pdf->SetFont('times', 'B', 11);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(8, 7, 'NO', 1, 0, 'C', true);
            $pdf->Cell(70, 7, 'MATA PELAJARAN', 1, 0, 'C', true);
            $pdf->Cell(15, 7, 'KKM', 1, 0, 'C', true);
            $pdf->Cell(15, 7, 'NILAI', 1, 0, 'C', true);
            $pdf->Cell(72, 7, 'DESKRIPSI', 1, 1, 'C', true);

            $getGradeText = function ($gradeValue) {
                if ($gradeValue >= 90) return 'Sangat Baik';
                if ($gradeValue >= 80) return 'Baik';
                if ($gradeValue >= 70) return 'Cukup';
                return 'Kurang';
            };

            // Tampilkan nilai per kategori
            $no = 1;
            $pdf->SetFont('times', '', 9);

            foreach ($groupedByCategory as $categoryId => $grades) {
                // Skip kategori uncategorized (Lainnya)
                if ($categoryId === 'uncategorized') {
                    continue;
                }

                // Ambil nama kategori
                $categoryName = '';
                $firstGrade = $grades->first();
                if ($firstGrade && $firstGrade->subject && $firstGrade->subject->category) {
                    $categoryName = $firstGrade->subject->category->name;
                }

                // Skip jika tidak ada nama kategori
                if (empty($categoryName)) {
                    continue;
                }

                // Header kategori
                $pdf->SetFont('times', 'B', 11);
                $pdf->SetFillColor(230, 230, 230);
                $pdf->Cell(180, 7, $categoryName, 1, 1, 'L', true);

                // Group by subject dalam kategori
                $groupedBySubject = $grades->groupBy('subject_id');

                foreach ($groupedBySubject as $subjectId => $subjectGrades) {
                    $pdf->SetFont('times', '', 11);
                    $subject = $subjectGrades->first()->subject;
                    $mainGrade = $subjectGrades->first(); // Karena sudah difilter hanya main grade

                    $kkm = $subject->kkm ?? 75;

                    // Deskripsi
                    $gradeText = $getGradeText($mainGrade->grade);
                    $studentName = $student->user->name ?? 'siswa';
                    $subjectName = ucwords(strtolower($subject->name ?? 'mata pelajaran'));
                    $deskripsi = "Ananda {$studentName} {$gradeText} dalam mengingat dan memahami isi materi {$subjectName}";

                    // Simpan posisi awal
                    $startY = $pdf->GetY();
                    $startX = 15;

                    // Hitung tinggi yang diperlukan untuk deskripsi
                    $pdf->SetXY($startX + 108, $startY); // Posisi kolom deskripsi
                    $cellHeight = $pdf->getStringHeight(72, $deskripsi);
                    $rowHeight = max($cellHeight, 7); // Minimal 7

                    // Gambar semua cell dengan tinggi yang sama
                    $pdf->SetXY($startX, $startY);
                    $pdf->Cell(8, $rowHeight, $no++, 'LRT', 0, 'C');
                    $pdf->Cell(70, $rowHeight, $subject->name ?? '-', 'LRT', 0, 'L');
                    $pdf->Cell(15, $rowHeight, $kkm, 'LRT', 0, 'C');
                    $pdf->Cell(15, $rowHeight, $mainGrade->grade, 'LRT', 0, 'C');

                    // MultiCell untuk deskripsi
                    $pdf->MultiCell(72, 3.5, $deskripsi, 1, 'L');
                }
            }

            // Kehadiran (dalam tabel yang sama)
            $pdf->SetFont('times', 'B', 11);
            $pdf->SetFillColor(230, 230, 230);
            $pdf->Cell(180, 7, 'Ketidakhadiran', 1, 1, 'L', true);

            $pdf->SetFont('times', '', 11);
            $pdf->Cell(78, 7, 'Sakit', 1, 0, 'L');
            $pdf->Cell(30, 7, $sakitCount == 0 ? '-' : $sakitCount, 1, 0, 'C');
            $pdf->Cell(72, 7, 'Hari', 1, 1, 'L');

            $pdf->Cell(78, 7, 'Izin', 1, 0, 'L');
            $pdf->Cell(30, 7, $izinCount == 0 ? '-' : $izinCount, 1, 0, 'C');
            $pdf->Cell(72, 7, 'Hari', 1, 1, 'L');

            $pdf->Cell(78, 7, 'Tanpa Keterangan', 1, 0, 'L');
            $pdf->Cell(30, 7, $alphaCount == 0 ? '-' : $alphaCount, 1, 0, 'C');
            $pdf->Cell(72, 7, 'Hari', 1, 1, 'L');
            // Catatan Wali Kelas (di bawah kehadiran)
            $pdf->SetFont('times', 'B', 11);
            $pdf->Cell(180, 7, 'Catatan Wali Kelas', 1, 1, 'L', true);

            $comment = $reportCard->teacher_comment ?? '';
            $pdf->SetFont('times', '', 11);
            $pdf->MultiCell(180, 7, $comment, 1, 'L');
            $pdf->Ln(7);

            // Tanda tangan - Ambil nama wali kelas
            $homeroomTeacherName = '';
            if ($student->classModel && $student->classModel->homeroomTeacher) {
                $homeroomTeacherName = $student->classModel->homeroomTeacher->user->name ?? '';
            }

            $pdf->SetFont('times', '', 11);
            $pdf->Cell(60, 5, '', 0, 0, 'C');
            $pdf->Cell(60, 5, '', 0, 0, 'C');
            $pdf->Cell(60, 5, 'Lamongan, ' . $this->formatIndonesianDate(now()), 0, 1, 'C');

            $pdf->Cell(60, 5, 'Wali Kelas', 0, 0, 'C');
            $pdf->Cell(60, 5, 'Orang Tua/Wali', 0, 0, 'C');
            $pdf->Cell(60, 5, 'Kepala Madrasah', 0, 1, 'C');

            $pdf->Ln(20);

            $pdf->Cell(60, 5, '( ' . $homeroomTeacherName . ' )', 0, 0, 'C');
            $pdf->Cell(60, 5, '( ........................... )', 0, 0, 'C');
            $pdf->Cell(60, 5, '( FU\'AD, M.Pd.I )', 0, 1, 'C');
        }

        // HALAMAN KEDUA: RAPOR TAHFIDZ (DETAIL)
        $pdf->AddPage();

        // Header ulang
        $pdf->Image(public_path('img/logo.png'), 22, 2, 38, 38);

        $pdf->SetFont('algerian', 'B', 22);
        $pdf->SetXY(32, 9);
        $pdf->Cell(180, 10, 'M A T A Z', 0, 1, 'C');

        $pdf->SetFont('comicsansms', 'B', 18);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetX(32);
        $pdf->Cell(180, 8, '( Markaz Tahfidz El-Zahro )', 0, 1, 'C');

        $pdf->SetFont('times', '', 11);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetX(32);
        $pdf->Cell(180, 5, 'Dsn. Poncol, Ds. Banjarejo, Kec. Karangbinangun, Kab. Lamongan', 0, 1, 'C');
        $pdf->SetX(32);
        $pdf->Cell(180, 5, 'HP. 081330578575/081332222366 | Email: markaztahfidzelzahroh@gmail.com', 0, 1, 'C');

        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.2);
        $pdf->Line(14.8, 38, 195.2, 38);
        $pdf->SetLineWidth(0.7);
        $pdf->Line(15, 38.7, 195, 38.7);
        $pdf->SetLineWidth(0.2);

        $pdf->Ln(3);

        $pdf->SetFont('freeserif', 'B', 16);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 10, 'بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ', 0, 1, 'C');

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('times', '', 14);
        $pdf->Cell(0, 8, 'TRANSKRIP NILAI AKHIR LEVEL', 0, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetFont('times', '', 12);
        $y_start = $pdf->GetY() + 4;

        $pdf->SetXY(15, $y_start);
        $pdf->Cell(35, 6, 'Nama', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'C');
        $pdf->SetFont('times', '', 12);
        $pdf->Cell(70, 6, $student->user->name ?? '-', 0, 0, 'L');

        $pdf->SetXY(15, $y_start + 7);
        $pdf->SetFont('times', '', 12);
        $pdf->Cell(35, 6, 'No. Induk', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'C');
        $pdf->SetFont('times', '', 12);
        $pdf->Cell(70, 6, $student->nis ?? '-', 0, 0, 'L');

        $pdf->SetXY(15, $y_start + 14);
        $pdf->SetFont('times', '', 12);
        $pdf->Cell(35, 6, 'Kelas/Level', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'C');
        $pdf->SetFont('times', '', 12);
        $pdf->Cell(70, 6, $student->classModel->name ?? '-', 0, 0, 'L');

        $pdf->SetXY(15, $y_start + 21);
        $pdf->SetFont('times', '', 12);
        $pdf->Cell(35, 6, 'Alamat', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'C');
        $pdf->SetFont('times', '', 12);
        $pdf->Cell(70, 6, $student->address ?? '-', 0, 0, 'L');

        $pdf->SetXY(15, $y_start + 28);
        $pdf->SetFont('times', '', 12);
        $pdf->Cell(35, 6, 'Wali Murid', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'C');
        $pdf->SetFont('times', '', 12);
        $pdf->Cell(70, 6, $student->wali ?? '-', 0, 0, 'L');

        $pdf->SetY($y_start + 40);

        if ($reportCard && $reportCard->grades->count() > 0) {
            // Filter hanya grades dari subject yang is_tahfidz = true
            $tahfidzGrades = $reportCard->grades->filter(function ($grade) {
                return $grade->subject && $grade->subject->is_tahfidz;
            });

            $groupedGrades = $tahfidzGrades->groupBy('subject_id');
            $juz30Grades = null;
            $juz29Grades = null;
            $otherGrades = collect();

            $sortedGrades = $groupedGrades->sortBy(function ($grades) {
                $subject = $grades->first()->subject;
                $name = strtolower($subject->name);
                if (str_contains($name, 'juz 30')) return 1;
                if (str_contains($name, 'juz 29')) return 2;
                if (str_contains($name, 'juz ')) {
                    if (preg_match('/juz (\d+)/', $name, $matches)) {
                        return 100 + (int) $matches[1];
                    }
                    return 999;
                }
                return 1000;
            });

            foreach ($sortedGrades as $subjectId => $grades) {
                $subject = $grades->first()->subject;
                if (str_contains(strtolower($subject->name), 'juz 30')) {
                    $juz30Grades = $grades;
                } elseif (str_contains(strtolower($subject->name), 'juz 29')) {
                    $juz29Grades = $grades;
                } else {
                    $otherGrades->put($subjectId, $grades);
                }
            }

            if ($juz30Grades) {

                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('times', '', 12);

                $pdf->Cell(45, 8, 'Obyek Penilaian', 1, 0, 'C');
                $pdf->Cell(45, 8, 'Nilai', 1, 0, 'C');
                $pdf->Cell(45, 8, 'Obyek Penilaian', 1, 0, 'C');
                $pdf->Cell(45, 8, 'Nilai', 1, 1, 'C');

                $pdf->SetFont('times', '', 12);
                $pdf->Cell(45, 6, 'Juz 30', 1, 0, 'C');
                $pdf->Cell(15, 6, 'Angka', 1, 0, 'C');
                $pdf->Cell(30, 6, 'Keterangan', 1, 0, 'C');
                $pdf->Cell(45, 6, 'Juz 30', 1, 0, 'C');
                $pdf->Cell(15, 6, 'Angka', 1, 0, 'C');
                $pdf->Cell(30, 6, 'Keterangan', 1, 1, 'C');

                $juz30Array = [];
                foreach ($juz30Grades as $grade) {
                    if ($grade->subject_detail_id) {
                        $juz30Array[] = $grade;
                    }
                }

                $totalGrades = count($juz30Array);
                $halfCount = ceil($totalGrades / 2);

                $getGradeInfo = function ($gradeValue) {
                    if ($gradeValue == 100) return ['Istimewa', [144, 238, 144]];
                    if ($gradeValue >= 90) return ['Sangat Baik', [144, 238, 144]];
                    if ($gradeValue >= 80) return ['Baik', [173, 216, 230]];
                    if ($gradeValue >= 70) return ['Cukup', [255, 255, 224]];
                    return ['Kurang', [255, 182, 193]];
                };

                for ($i = 0; $i < $halfCount; $i++) {
                    if (isset($juz30Array[$i])) {
                        $leftGrade = $juz30Array[$i];
                        $leftGradeValue = $leftGrade->grade;
                        $leftGradeInfo = $getGradeInfo($leftGradeValue);
                        $leftSurahName = $leftGrade->subjectDetail->name ?? '-';

                        $leftArabicName = preg_match('/^([^\(]+)/', $leftSurahName, $matches)
                            ? trim($matches[1])
                            : $leftSurahName;

                        $pdf->SetFont('times', '', 12);
                        $pdf->Cell(10, 6, ($i + 1), 1, 0, 'C', true);
                        $pdf->SetFont('freeserif', '', 12);
                        $pdf->Cell(35, 6, $leftArabicName, 1, 0, 'C', true);
                        $pdf->SetFont('times', '', 12);
                        $pdf->Cell(15, 6, $leftGradeValue, 1, 0, 'C', true);
                        $pdf->SetFont('times', '', 12);
                        $pdf->Cell(30, 6, $leftGradeInfo[0], 1, 0, 'C', true);
                    } else {
                        $pdf->SetFillColor(255, 255, 255);
                        $pdf->Cell(10, 6, '', 1, 0, 'C', true);
                        $pdf->Cell(35, 6, '', 1, 0, 'C', true);
                        $pdf->Cell(15, 6, '', 1, 0, 'C', true);
                        $pdf->Cell(30, 6, '', 1, 0, 'C', true);
                    }

                    $rightIndex = $i + $halfCount;
                    if (isset($juz30Array[$rightIndex])) {
                        $rightGrade = $juz30Array[$rightIndex];
                        $rightGradeValue = $rightGrade->grade;
                        $rightGradeInfo = $getGradeInfo($rightGradeValue);
                        $rightSurahName = $rightGrade->subjectDetail->name ?? '-';

                        $rightArabicName = preg_match('/^([^\(]+)/', $rightSurahName, $matches)
                            ? trim($matches[1])
                            : $rightSurahName;

                        $pdf->SetFont('times', '', 12);
                        $pdf->Cell(10, 6, ($rightIndex + 1), 1, 0, 'C', true);
                        $pdf->SetFont('freeserif', '', 12);
                        $pdf->Cell(35, 6, $rightArabicName, 1, 0, 'C', true);
                        $pdf->SetFont('times', '', 12);
                        $pdf->Cell(15, 6, $rightGradeValue, 1, 0, 'C', true);
                        $pdf->SetFont('times', '', 12);
                        $pdf->Cell(30, 6, $rightGradeInfo[0], 1, 1, 'C', true);
                    } else {
                        $pdf->SetFillColor(255, 255, 255);
                        $pdf->Cell(10, 6, '', 1, 0, 'C', true);
                        $pdf->Cell(35, 6, '', 1, 0, 'C', true);
                        $pdf->Cell(15, 6, '', 1, 0, 'C', true);
                        $pdf->Cell(30, 6, '', 1, 1, 'C', true);
                    }
                }

                $pdf->SetFillColor(0, 0, 0);
                $pdf->Cell(90, 6, '', 1, 0, 'C', true);
                $pdf->Cell(45, 6, '', 1, 0, 'C', true);
                $pdf->Cell(45, 6, '', 1, 1, 'C', true);

                $totalNilai = collect($juz30Array)->sum('grade');
                $rataRata = count($juz30Array) > 0 ? round($totalNilai / count($juz30Array), 1) : 0;

                $pdf->SetFillColor(255, 255, 255);
                $pdf->SetFont('times', '', 12);
                $pdf->Cell(90, 7, 'Total Nilai', 1, 0, 'L', true);
                $pdf->SetFont('times', 'B', 12);
                $pdf->Cell(90, 7, $totalNilai, 1, 1, 'L', true);

                $pdf->SetFont('times', '', 12);
                $pdf->Cell(90, 7, 'Rata-rata', 1, 0, 'L', true);
                $pdf->SetFont('times', 'B', 12);
                $pdf->Cell(90, 7, $rataRata, 1, 1, 'L', true);

                $pdf->SetFillColor(0, 0, 0);
                $pdf->Cell(90, 6, '', 1, 0, 'C', true);
                $pdf->Cell(45, 6, '', 1, 0, 'C', true);
                $pdf->Cell(45, 6, '', 1, 1, 'C', true);
            }

            if ($juz29Grades) {
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('times', '', 12);

                $pdf->Cell(45, 8, 'Obyek Penilaian', 1, 0, 'C');
                $pdf->Cell(45, 8, 'Nilai', 1, 0, 'C');
                $pdf->Cell(45, 8, 'Obyek Penilaian', 1, 0, 'C');
                $pdf->Cell(45, 8, 'Nilai', 1, 1, 'C');

                $pdf->Cell(45, 6, 'Juz 29', 1, 0, 'C');
                $pdf->Cell(15, 6, 'Angka', 1, 0, 'C');
                $pdf->Cell(30, 6, 'Keterangan', 1, 0, 'C');
                $pdf->Cell(45, 6, 'Juz 29', 1, 0, 'C');
                $pdf->Cell(15, 6, 'Angka', 1, 0, 'C');
                $pdf->Cell(30, 6, 'Keterangan', 1, 1, 'C');

                $juz29Array = [];
                foreach ($juz29Grades as $grade) {
                    if ($grade->subject_detail_id) {
                        $juz29Array[] = $grade;
                    }
                }

                $totalGrades = count($juz29Array);
                $halfCount = ceil($totalGrades / 2);

                $getGradeInfo = function ($gradeValue) {
                    if ($gradeValue == 100) return ['Istimewa', [144, 238, 144]];
                    if ($gradeValue >= 90) return ['Sangat Baik', [144, 238, 144]];
                    if ($gradeValue >= 80) return ['Baik', [173, 216, 230]];
                    if ($gradeValue >= 70) return ['Cukup', [255, 255, 224]];
                    return ['Kurang', [255, 182, 193]];
                };

                for ($i = 0; $i < $halfCount; $i++) {
                    if (isset($juz29Array[$i])) {
                        $leftGrade = $juz29Array[$i];
                        $leftGradeValue = $leftGrade->grade;
                        $leftGradeInfo = $getGradeInfo($leftGradeValue);
                        $leftSurahName = $leftGrade->subjectDetail->name ?? '-';

                        $leftArabicName = preg_match('/^([^\(]+)/', $leftSurahName, $matches)
                            ? trim($matches[1])
                            : $leftSurahName;

                        $pdf->SetFont('times', '', 12);
                        $pdf->Cell(10, 6, ($i + 1), 1, 0, 'C');
                        $pdf->SetFont('freeserif', '', 12);
                        $pdf->Cell(35, 6, $leftArabicName, 1, 0, 'C');
                        $pdf->SetFont('times', '', 12);
                        $pdf->Cell(15, 6, $leftGradeValue, 1, 0, 'C');
                        $pdf->Cell(30, 6, $leftGradeInfo[0], 1, 0, 'C');
                    } else {
                        $pdf->SetFillColor(255, 255, 255);
                        $pdf->Cell(10, 6, '', 1, 0, 'C', true);
                        $pdf->Cell(35, 6, '', 1, 0, 'C', true);
                        $pdf->Cell(15, 6, '', 1, 0, 'C', true);
                        $pdf->Cell(30, 6, '', 1, 0, 'C', true);
                    }

                    $rightIndex = $i + $halfCount;
                    if (isset($juz29Array[$rightIndex])) {
                        $rightGrade = $juz29Array[$rightIndex];
                        $rightGradeValue = $rightGrade->grade;
                        $rightGradeInfo = $getGradeInfo($rightGradeValue);
                        $rightSurahName = $rightGrade->subjectDetail->name ?? '-';

                        $rightArabicName = preg_match('/^([^\(]+)/', $rightSurahName, $matches)
                            ? trim($matches[1])
                            : $rightSurahName;

                        $pdf->Cell(10, 6, ($rightIndex + 1), 1, 0, 'C');
                        $pdf->SetFont('freeserif', '', 12);
                        $pdf->Cell(35, 6, $rightArabicName, 1, 0, 'C');
                        $pdf->SetFont('times', '', 12);
                        $pdf->Cell(15, 6, $rightGradeValue, 1, 0, 'C');
                        $pdf->Cell(30, 6, $rightGradeInfo[0], 1, 1, 'C');
                    } else {
                        $pdf->SetFillColor(255, 255, 255);
                        $pdf->Cell(10, 6, '', 1, 0, 'C', true);
                        $pdf->Cell(35, 6, '', 1, 0, 'C', true);
                        $pdf->Cell(15, 6, '', 1, 0, 'C', true);
                        $pdf->Cell(30, 6, '', 1, 1, 'C', true);
                    }
                }

                $pdf->SetFillColor(0, 0, 0);
                $pdf->Cell(90, 6, '', 1, 0, 'C', true);
                $pdf->Cell(45, 6, '', 1, 0, 'C', true);
                $pdf->Cell(45, 6, '', 1, 1, 'C', true);

                $totalNilai = collect($juz29Array)->sum('grade');
                $rataRata = count($juz29Array) > 0 ? round($totalNilai / count($juz29Array), 1) : 0;

                $pdf->SetFillColor(255, 255, 255);
                $pdf->SetFont('times', '', 12);
                $pdf->Cell(90, 7, 'Total Nilai', 1, 0, 'L', true);
                $pdf->SetFont('times', 'B', 12);
                $pdf->Cell(90, 7, $totalNilai, 1, 1, 'L', true);

                $pdf->SetFont('times', '', 12);
                $pdf->Cell(90, 7, 'Rata-rata', 1, 0, 'L', true);
                $pdf->SetFont('times', 'B', 12);
                $pdf->Cell(90, 7, $rataRata, 1, 1, 'L', true);

                $pdf->SetFillColor(0, 0, 0);
                $pdf->Cell(90, 6, '', 1, 0, 'C', true);
                $pdf->Cell(45, 6, '', 1, 0, 'C', true);
                $pdf->Cell(45, 6, '', 1, 1, 'C', true);
            }

            // Tampilkan bagian "Obyek Penilaian Per Juz" jika ada other grades
            if ($otherGrades->count() > 0) {
                $allOtherDetailGrades = collect();
                $allOtherMainGrades = collect();

                foreach ($otherGrades as $subjectId => $grades) {
                    $mainGrade = $grades->where('subject_detail_id', null)->first();
                    if ($mainGrade) {
                        $allOtherMainGrades->push($mainGrade);
                    }

                    $detailGrades = $grades->where('subject_detail_id', '!=', null);
                    $allOtherDetailGrades = $allOtherDetailGrades->merge($detailGrades);
                }

                $totalOtherGrades = $allOtherMainGrades->sum('grade');
                $averageOtherGrades = $allOtherMainGrades->count() > 0
                    ? round($totalOtherGrades / $allOtherMainGrades->count(), 1)
                    : 0;

                $startY = $pdf->GetY();

                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('times', '', 12);

                $pdf->SetXY(15, $startY);
                $pdf->Cell(90, 14, 'Obyek Penilaian Per Juz', 1, 0, 'C');

                $pdf->SetXY(105, $startY);
                $pdf->Cell(90, 8, 'Nilai', 1, 1, 'C');

                $pdf->SetXY(105, $startY + 8);
                $pdf->Cell(45, 6, 'Angka', 1, 0, 'C');
                $pdf->Cell(45, 6, 'Keterangan', 1, 1, 'C');

                $pdf->SetY($startY + 14);

                $getGradeInfo = function ($gradeValue) {
                    if ($gradeValue == 100) return ['Istimewa', [144, 238, 144]];
                    if ($gradeValue >= 90) return ['Sangat Baik', [144, 238, 144]];
                    if ($gradeValue >= 80) return ['Baik', [173, 216, 230]];
                    if ($gradeValue >= 70) return ['Cukup', [255, 255, 224]];
                    return ['Kurang', [255, 182, 193]];
                };

                $pdf->SetFont('freeserif', '', 12);
                foreach ($otherGrades as $subjectId => $grades) {
                    $subject = $grades->first()->subject;
                    $mainGrade = $grades->where('subject_detail_id', null)->first();
                    $gradeInfo = $getGradeInfo($mainGrade ? $mainGrade->grade : 0);

                    $pdf->SetFont('freeserif', '', 12);
                    $pdf->Cell(90, 7, strtoupper($subject->name), 1, 0, 'L');
                    $pdf->SetFont('times', '', 12);
                    $pdf->Cell(45, 7, $mainGrade ? $mainGrade->grade : '-', 1, 0, 'C');
                    $pdf->Cell(45, 7, $gradeInfo[0], 1, 1, 'C');
                }

                $pdf->SetFillColor(0, 0, 0);
                $pdf->Cell(90, 6, '', 1, 0, 'C', true);
                $pdf->Cell(45, 6, '', 1, 0, 'C', true);
                $pdf->Cell(45, 6, '', 1, 1, 'C', true);

                $pdf->SetFont('times', '', 12);
                $pdf->Cell(90, 7, 'Total Nilai', 1, 0, 'L');
                $pdf->SetFont('times', 'B', 12);
                $pdf->Cell(90, 7, $totalOtherGrades, 1, 1, 'L');

                $pdf->SetFont('times', '', 12);
                $pdf->Cell(90, 7, 'Rata-rata', 1, 0, 'L');
                $pdf->SetFont('times', 'B', 12);
                $pdf->Cell(90, 7, $averageOtherGrades, 1, 1, 'L');

                $pdf->SetFillColor(0, 0, 0);
                $pdf->Cell(90, 6, '', 1, 0, 'C', true);
                $pdf->Cell(45, 6, '', 1, 0, 'C', true);
                $pdf->Cell(45, 6, '', 1, 1, 'C', true);
            }

            // Hitung total nilai dari semua grades tahfidz
            $totalTahfidzScore = $tahfidzGrades->sum('grade');
            $totalTahfidzGrades = $tahfidzGrades->count();
            $averageTahfidzScore = $totalTahfidzGrades > 0 ? round($totalTahfidzScore / $totalTahfidzGrades, 1) : 0;

            // Total Nilai Keseluruhan dan Absensi (selalu ditampilkan)
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('times', 'B', 12);
            $pdf->Cell(90, 7, 'Total Nilai Keseluruhan', 1, 0, 'L', true);
            $pdf->Cell(90, 7, $totalTahfidzScore, 1, 1, 'L', true);

            $pdf->Cell(90, 7, 'Rata-rata Keseluruhan', 1, 0, 'L', true);
            $pdf->Cell(90, 7, $averageTahfidzScore, 1, 1, 'L', true);

            $pdf->SetFont('times', '', 12);
            $pdf->Cell(180, 7, 'Absensi Kehadiran', 1, 1, 'L', true);

            $pdf->Cell(90, 6, 'Izin', 1, 0, 'L', true);
            $pdf->Cell(45, 6, $izinCount == 0 ? '-' : $izinCount, 1, 0, 'C', true);
            $pdf->Cell(45, 6, 'Hari', 1, 1, 'L', true);

            $pdf->Cell(90, 6, 'Sakit', 1, 0, 'L', true);
            $pdf->Cell(45, 6, $sakitCount == 0 ? '-' : $sakitCount, 1, 0, 'C', true);
            $pdf->Cell(45, 6, 'Hari', 1, 1, 'L', true);

            $pdf->Cell(90, 6, 'Absen', 1, 0, 'L', true);
            $pdf->Cell(45, 6, $alphaCount == 0 ? '-' : $alphaCount, 1, 0, 'C', true);
            $pdf->Cell(45, 6, 'Hari', 1, 1, 'L', true);

            $pdf->Cell(90, 6, 'Jumlah', 1, 0, 'L', true);
            $pdf->Cell(45, 6, $totalAbsent == 0 ? '-' : $totalAbsent, 1, 0, 'C', true);
            $pdf->Cell(45, 6, 'Hari', 1, 1, 'L', true);

            $pdf->SetFillColor(0, 0, 0);
            $pdf->Cell(90, 6, '', 1, 0, 'C', true);
            $pdf->Cell(45, 6, '', 1, 0, 'C', true);
            $pdf->Cell(45, 6, '', 1, 1, 'C', true);

            $pdf->Cell(90, 7, 'Keterangan Kelulusan', 1, 0, 'L');
            $pdf->Cell(90, 7, '', 1, 1, 'L');

            $comment = $reportCard->teacher_comment ?? '-';
            $pdf->SetFont('times', '', 12);

            $y = $pdf->GetY();
            $pdf->SetXY(15, $y);

            $pdf->Rect(15, $y, 180, 15);
            $pdf->SetXY(15, $y + 2);
            $pdf->MultiCell(176, 4, 'Pesan Fasilitator:' . "\n" . $comment, 0, 'L');

            $pdf->SetY($y + 17);

            $pdf->Ln(3);
        }

        $pdf->Ln(6);
        $homeroomTeacherName = '';
        if ($student->classModel && $student->classModel->homeroomTeacher) {
            $homeroomTeacherName = $student->classModel->homeroomTeacher->user->name ?? '';
        }

        $pdf->SetFont('times', '', 12);
        $pdf->Cell(60, 5, '', 0, 0, 'C');
        $pdf->Cell(60, 5, '', 0, 0, 'C');
        $pdf->Cell(60, 5, 'Lamongan, ' . $this->formatIndonesianDate(now()), 0, 1, 'C');

        $pdf->Cell(60, 5, 'Wali Kelas', 0, 0, 'C');
        $pdf->Cell(60, 5, 'Orang Tua/Wali', 0, 0, 'C');
        $pdf->Cell(60, 5, 'Kepala Madrasah', 0, 1, 'C');

        $pdf->Ln(20);

        $pdf->Cell(60, 5, '( ' . $homeroomTeacherName . ' )', 0, 0, 'C');
        $pdf->Cell(60, 5, '( ........................... )', 0, 0, 'C');
        $pdf->Cell(60, 5, '( FU\'AD, M.Pd.I )', 0, 1, 'C');

        $filename = 'Rapor_' . str_replace([' ', '.', ','], '_', $student->user->name) . '_' . now()->format('Y-m-d') . '.pdf';

        return response($pdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }
}
