<?php

namespace App\Http\Controllers;

use App\Models\ReportCard;
use App\Models\Semester;
use App\Models\Student;
use Exception;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function generateReport($studentId)
    {
        $student = Student::findOrFail($studentId);
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
            ->with(['semester.academicYear', 'grades.subject', 'grades.subjectDetail'])
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

        $pdf->SetCreator('MATAZ El Zahroh');
        $pdf->SetTitle('Rapor ' . $student->user->name);

        $pdf->SetMargins(15, 15, 15);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(true, 15);

        $pdf->AddPage();

        $pdf->Image(public_path('img/logo.png'), 22, 2, 38, 38);

        $pdf->SetFont('algerian', 'B', 22);
        $pdf->SetXY(32, 9);
        $pdf->Cell(180, 10, 'M A T A Z', 0, 1, 'C');

        $pdf->SetFont('comicsansms', 'B', 18);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetX(32);
        $pdf->Cell(180, 8, '( Markaz Tahfidz El-Zahro )', 0, 1, 'C');

        $pdf->SetFont('times', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetX(32);
        $pdf->Cell(180, 5, 'Dsn. Poncol, Ds. Banjarejo, Kec. Karangbinangun, Kab. Lamongan', 0, 1, 'C');
        $pdf->SetX(32);
        $pdf->Cell(180, 5, 'HP. 081330578575/081332222366 | Email: markaztahfidzelzahroh@gmail.com', 0, 1, 'C');

        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.3);
        $pdf->Line(15, 38, 195, 38);
        $pdf->SetLineWidth(1.2);
        $pdf->Line(15, 39, 195, 39);
        $pdf->SetLineWidth(0.3);

        $pdf->Ln(3);

        $pdf->SetFont('freeserif', 'B', 20);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 10, 'بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ', 0, 1, 'C');

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('times', '', 14);
        $pdf->Cell(0, 8, 'TRANSKRIP NILAI AKHIR LEVEL', 0, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetFont('times', '', 13);
        $y_start = $pdf->GetY() + 4;

        $pdf->SetXY(15, $y_start);
        $pdf->Cell(35, 6, 'Nama', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'C');
        $pdf->SetFont('times', '', 13);
        $pdf->Cell(70, 6, $student->user->name ?? '-', 0, 0, 'L');

        $pdf->SetXY(15, $y_start + 7);
        $pdf->SetFont('times', '', 13);
        $pdf->Cell(35, 6, 'No. Induk', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'C');
        $pdf->SetFont('times', '', 13);
        $pdf->Cell(70, 6, $student->nis ?? '-', 0, 0, 'L');

        $pdf->SetXY(15, $y_start + 14);
        $pdf->SetFont('times', '', 13);
        $pdf->Cell(35, 6, 'Kelas/Level', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'C');
        $pdf->SetFont('times', '', 13);
        $pdf->Cell(70, 6, $student->classModel->name ?? '-', 0, 0, 'L');

        $pdf->SetXY(15, $y_start + 21);
        $pdf->SetFont('times', '', 13);
        $pdf->Cell(35, 6, 'Alamat', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'C');
        $pdf->SetFont('times', '', 13);
        $pdf->Cell(70, 6, $student->classModel->name ?? '-', 0, 0, 'L');

        $pdf->SetXY(15, $y_start + 28);
        $pdf->SetFont('times', '', 13);
        $pdf->Cell(35, 6, 'Wali Murid', 0, 0, 'L');
        $pdf->Cell(5, 6, ':', 0, 0, 'C');
        $pdf->SetFont('times', '', 13);
        $pdf->Cell(70, 6, $student->wali ?? '-', 0, 0, 'L');

        $pdf->SetY($y_start + 40);

        if ($reportCard && $reportCard->grades->count() > 0) {
            $groupedGrades = $reportCard->grades->groupBy('subject_id');
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
                $pdf->SetFont('times', '', 13);

                $pdf->Cell(45, 8, 'Obyek Penilaian', 1, 0, 'C');
                $pdf->Cell(45, 8, 'Nilai', 1, 0, 'C');
                $pdf->Cell(45, 8, 'Obyek Penilaian', 1, 0, 'C');
                $pdf->Cell(45, 8, 'Nilai', 1, 1, 'C');

                $pdf->SetFont('times', '', 13);
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
                    if ($gradeValue >= 95) return ['Istimewa', [144, 238, 144]];
                    if ($gradeValue >= 85) return ['Sangat Baik', [173, 216, 230]];
                    if ($gradeValue >= 75) return ['Baik', [255, 255, 224]];
                    if ($gradeValue >= 65) return ['Cukup', [255, 228, 196]];
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

                        $pdf->SetFont('times', '', 13);
                        $pdf->Cell(10, 6, ($i + 1), 1, 0, 'C', true);
                        $pdf->SetFont('freeserif', '', 13);
                        $pdf->Cell(35, 6, $leftArabicName, 1, 0, 'C', true);
                        $pdf->SetFont('times', '', 13);
                        $pdf->Cell(15, 6, $leftGradeValue, 1, 0, 'C', true);
                        $pdf->SetFont('times', '', 13);
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

                        $pdf->SetFont('times', '', 13);
                        $pdf->Cell(10, 6, ($rightIndex + 1), 1, 0, 'C', true);
                        $pdf->SetFont('freeserif', '', 13);
                        $pdf->Cell(35, 6, $rightArabicName, 1, 0, 'C', true);
                        $pdf->SetFont('times', '', 13);
                        $pdf->Cell(15, 6, $rightGradeValue, 1, 0, 'C', true);
                        $pdf->SetFont('times', '', 13);
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
                $pdf->SetFont('times', '', 13);
                $pdf->Cell(90, 7, 'Total Nilai', 1, 0, 'L', true);
                $pdf->SetFont('times', 'B', 13);
                $pdf->Cell(90, 7, $totalNilai, 1, 1, 'L', true);

                $pdf->SetFont('times', '', 13);
                $pdf->Cell(90, 7, 'Rata-rata', 1, 0, 'L', true);
                $pdf->SetFont('times', 'B', 13);
                $pdf->Cell(90, 7, $rataRata, 1, 1, 'L', true);

                $pdf->SetFillColor(0, 0, 0);
                $pdf->Cell(90, 6, '', 1, 0, 'C', true);
                $pdf->Cell(45, 6, '', 1, 0, 'C', true);
                $pdf->Cell(45, 6, '', 1, 1, 'C', true);
            }

            if ($juz29Grades) {
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('times', '', 13);

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
                    if ($gradeValue >= 95) return ['Istimewa', [144, 238, 144]];
                    if ($gradeValue >= 85) return ['Sangat Baik', [173, 216, 230]];
                    if ($gradeValue >= 75) return ['Baik', [255, 255, 224]];
                    if ($gradeValue >= 65) return ['Cukup', [255, 228, 196]];
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

                        $pdf->SetFont('times', '', 13);
                        $pdf->Cell(10, 6, ($i + 1), 1, 0, 'C');
                        $pdf->SetFont('freeserif', '', 13);
                        $pdf->Cell(35, 6, $leftArabicName, 1, 0, 'C');
                        $pdf->SetFont('times', '', 13);
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
                        $pdf->SetFont('freeserif', '', 13);
                        $pdf->Cell(35, 6, $rightArabicName, 1, 0, 'C');
                        $pdf->SetFont('times', '', 13);
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
                $pdf->SetFont('times', '', 13);
                $pdf->Cell(90, 7, 'Total Nilai', 1, 0, 'L', true);
                $pdf->SetFont('times', 'B', 13);
                $pdf->Cell(90, 7, $totalNilai, 1, 1, 'L', true);

                $pdf->SetFont('times', '', 13);
                $pdf->Cell(90, 7, 'Rata-rata', 1, 0, 'L', true);
                $pdf->SetFont('times', 'B', 13);
                $pdf->Cell(90, 7, $rataRata, 1, 1, 'L', true);

                $pdf->SetFillColor(0, 0, 0);
                $pdf->Cell(90, 6, '', 1, 0, 'C', true);
                $pdf->Cell(45, 6, '', 1, 0, 'C', true);
                $pdf->Cell(45, 6, '', 1, 1, 'C', true);
            }

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
                $pdf->SetFont('times', '', 13);

                $pdf->SetXY(15, $startY);
                $pdf->Cell(90, 14, 'Obyek Penilaian Per Juz', 1, 0, 'C');

                $pdf->SetXY(105, $startY);
                $pdf->Cell(90, 8, 'Nilai', 1, 1, 'C');

                $pdf->SetXY(105, $startY + 8);
                $pdf->Cell(45, 6, 'Angka', 1, 0, 'C');
                $pdf->Cell(45, 6, 'Keterangan', 1, 1, 'C');

                $pdf->SetY($startY + 14);

                $getGradeInfo = function ($gradeValue) {
                    if ($gradeValue >= 95) return ['Istimewa', [144, 238, 144]];
                    if ($gradeValue >= 85) return ['Sangat baik', [173, 216, 230]];
                    if ($gradeValue >= 75) return ['Baik', [255, 255, 224]];
                    if ($gradeValue >= 65) return ['Cukup', [255, 228, 196]];
                    return ['Kurang', [255, 182, 193]];
                };

                $pdf->SetFont('freeserif', '', 13);
                foreach ($otherGrades as $subjectId => $grades) {
                    $subject = $grades->first()->subject;
                    $mainGrade = $grades->where('subject_detail_id', null)->first();
                    $gradeInfo = $getGradeInfo($mainGrade ? $mainGrade->grade : 0);

                    $pdf->SetFont('freeserif', '', 13);
                    $pdf->Cell(90, 7, strtoupper($subject->name), 1, 0, 'L');
                    $pdf->SetFont('times', '', 13);
                    $pdf->Cell(45, 7, $mainGrade ? $mainGrade->grade : '-', 1, 0, 'C');
                    $pdf->Cell(45, 7, $gradeInfo[0], 1, 1, 'C');
                }

                $pdf->SetFillColor(0, 0, 0);
                $pdf->Cell(90, 6, '', 1, 0, 'C', true);
                $pdf->Cell(45, 6, '', 1, 0, 'C', true);
                $pdf->Cell(45, 6, '', 1, 1, 'C', true);

                $pdf->SetFont('times', '', 13);
                $pdf->Cell(90, 7, 'Total Nilai', 1, 0, 'L');
                $pdf->SetFont('times', 'B', 13);
                $pdf->Cell(90, 7, $totalOtherGrades, 1, 1, 'L');

                $pdf->SetFont('times', '', 13);
                $pdf->Cell(90, 7, 'Rata-rata', 1, 0, 'L');
                $pdf->SetFont('times', 'B', 13);
                $pdf->Cell(90, 7, $averageOtherGrades, 1, 1, 'L');

                $pdf->SetFillColor(0, 0, 0);
                $pdf->Cell(90, 6, '', 1, 0, 'C', true);
                $pdf->Cell(45, 6, '', 1, 0, 'C', true);
                $pdf->Cell(45, 6, '', 1, 1, 'C', true);

                $pdf->SetFillColor(255, 255, 255);
                $pdf->SetFont('times', 'B', 13);
                $pdf->Cell(90, 7, 'Total Nilai Keseluruhan', 1, 0, 'L', true);
                $pdf->Cell(90, 7, $totalScore, 1, 1, 'L', true);

                $pdf->Cell(90, 7, 'Rata-rata Keseluruhan', 1, 0, 'L', true);
                $pdf->Cell(90, 7, $averageScore, 1, 1, 'L', true);

                $pdf->SetFont('times', '', 13);
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
                $pdf->SetFont('times', '', 13);

                $y = $pdf->GetY();
                $pdf->SetXY(15, $y);

                $pdf->Rect(15, $y, 180, 15);
                $pdf->SetXY(15, $y + 2);
                $pdf->MultiCell(176, 4, 'Pesan Fasilitator:' . "\n" . $comment, 0, 'L');

                $pdf->SetY($y + 17);

                $pdf->Ln(3);
            }
        }

        $pdf->Ln(6);
        $pdf->SetFont('times', '', 13);

        $pdf->Cell(110, 6, '', 0, 0, 'C');
        $pdf->Cell(90, 6, 'Lamongan, ' . now()->locale('id')->format('d F Y'), 0, 1, 'L');
        $pdf->Ln(3);

        $signatureStartY = $pdf->GetY();

        $pdf->Cell(90, 6, 'Wali Murid', 0, 0, 'C');
        $pdf->Cell(90, 6, 'Ketua MATAZ El Zahroh', 0, 1, 'C');

        $pdf->Image(public_path('img/ttd-mataz.png'), 123, $pdf->GetY() + 1, 35, 35);

        $pdf->Ln(20);

        $pdf->SetFont('times', '', 13);
        $pdf->Cell(90, 6, '( .................................. )', 0, 0, 'C');
        $pdf->Cell(90, 6, '( FU\'AD, M.Pd.I )', 0, 1, 'C');

        $filename = 'Rapor_' . str_replace([' ', '.', ','], '_', $student->user->name) . '_' . now()->format('Y-m-d') . '.pdf';

        return response($pdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }
}
