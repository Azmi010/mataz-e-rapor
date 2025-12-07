<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\SubjectCategory;
use Exception;

class RecapController extends Controller
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

    public function generateRecap($studentId)
    {
        $student = Student::with(['classModel'])->findOrFail($studentId);

        $reportCards = $student
            ->reportCards()
            ->with(['semester.academicYear', 'grades.subject.category'])
            ->whereHas('semester.academicYear')
            ->get()
            ->sortBy(function ($reportCard) {
                $year = $reportCard->semester->academicYear->name ?? '0';
                $semesterType = $reportCard->semester->semester_type == 'Ganjil' ? 1 : 2;
                return $year . $semesterType;
            });

        $categories = SubjectCategory::orderBy('name')->get();

        $allSubjects = collect();
        foreach ($reportCards as $reportCard) {
            foreach ($reportCard->grades as $grade) {
                if ($grade->subject && !$grade->subject->is_tahfidz && $grade->subject_detail_id == null) {
                    $subjectId = $grade->subject->id;
                    if (!$allSubjects->has($subjectId)) {
                        $allSubjects->put($subjectId, $grade->subject);
                    }
                }
            }
        }

        $subjectsByCategory = $allSubjects->groupBy('category_id');

        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        try {
            \TCPDF_FONTS::addTTFfont(public_path('fonts/Algerian.ttf'), 'TrueTypeUnicode', '', 96);
            \TCPDF_FONTS::addTTFfont(public_path('fonts/ComicSansMS.ttf'), 'TrueTypeUnicode', '', 96);
        } catch (Exception $e) {
            error_log('Font loading failed: ' . $e->getMessage());
        }

        $pdf->SetCreator('MATAZ El Zahro');
        $pdf->SetTitle('Rekap Nilai ' . $student->user->name);

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
        $pdf->Line(15, 45, 195, 45);

        $pdf->Ln(3);

        // Data Siswa
        $pdf->SetFont('times', '', 11);
        $y = $pdf->GetY();

        $pdf->SetXY(15, $y);
        $pdf->Cell(20, 5, 'Nama', 0, 0);
        $pdf->Cell(3, 5, ':', 0, 0);
        $pdf->Cell(80, 5, $student->user->name ?? '-', 0, 0);

        $pdf->SetXY(115, $y);
        $pdf->Cell(25, 5, 'Madrasah', 0, 0);
        $pdf->Cell(3, 5, ':', 0, 0);
        $pdf->Cell(0, 5, 'MI Tahfidz El-Zahro', 0, 1);

        $pdf->SetX(15);
        $pdf->Cell(20, 5, 'NIS', 0, 0);
        $pdf->Cell(3, 5, ':', 0, 0);
        $pdf->Cell(80, 5, $student->nis ?? '-', 0, 0);

        $pdf->SetX(115);
        $pdf->Cell(25, 5, 'NISN', 0, 0);
        $pdf->Cell(3, 5, ':', 0, 0);
        $pdf->Cell(0, 5, $student->nisn ?? '-', 0, 1);

        $pdf->Ln(1);

        // Garis pemisah
        $pdf->SetLineWidth(0.5);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());

        $pdf->Ln(3);

        // Judul
        $pdf->SetFont('times', 'B', 13);
        $pdf->Cell(0, 6, 'REKAP HASIL BELAJAR', 0, 1, 'C');

        $pdf->Ln(3);

        $pdf->SetLineWidth(0.2);

        // Header Tabel
        $pdf->SetFont('times', 'B', 9);
        $pdf->SetFillColor(255, 255, 255);

        // Lebar kolom
        $noWidth = 6;
        $subjectWidth = 20;
        $semesterColWidth = 12;
        $avgWidth = 9;

        $totalSemesterWidth = $semesterColWidth * 12;

        // Baris 1: NO, Mata Pelajaran, Kelas I-VI, Rata-rata
        $y_start = $pdf->GetY();

        $pdf->SetXY(15, $y_start);
        $pdf->Cell($noWidth +$subjectWidth, 10, 'Mata Pelajaran', 1, 0, 'C', true);

        // Header kelas I sampai VI
        for ($i = 1; $i <= 6; $i++) {
            $pdf->Cell($semesterColWidth * 2, 5, $this->numberToRoman($i), 1, 0, 'C', true);
        }

        $pdf->MultiCell($avgWidth, 10, 'Rata-rata', 1, 'C', true, 1, '', '', true, 0, false, true, 10, 'M');

        // Baris 2: Sub-header Ganjil/Genap untuk setiap kelas
        $pdf->SetXY(15 + $noWidth + $subjectWidth, $y_start + 5);
        for ($i = 0; $i < 6; $i++) {
            $pdf->Cell($semesterColWidth, 5, 'Ganjil', 1, 0, 'C', true);
            $pdf->Cell($semesterColWidth, 5, 'Genap', 1, 0, 'C', true);
        }
        $pdf->Ln();

        // Isi Tabel
        $pdf->SetFont('times', '', 8);
        $no = 1;
        $allAverages = [];

        foreach ($categories as $category) {
            if (!$subjectsByCategory->has($category->id)) {
                continue;
            }

            // Header Kategori
            $pdf->SetFont('times', 'B', 10);
            $pdf->SetFillColor(255, 255, 255);
            $totalWidth = $noWidth + $subjectWidth + ($semesterColWidth * 12) + $avgWidth;
            $pdf->Cell($totalWidth, 6, $category->name, 1, 1, 'L', true);

            $pdf->SetFont('times', '', 8);
            $subjects = $subjectsByCategory->get($category->id)->sortBy('name');

            foreach ($subjects as $subject) {
                $grades = [];
                $total = 0;
                $count = 0;

                foreach ($reportCards as $index => $reportCard) {
                    if ($index >= 12) break;

                    $grade = $reportCard->grades->where('subject_id', $subject->id)
                        ->where('subject_detail_id', null)
                        ->first();

                    if ($grade) {
                        $grades[] = $grade->grade;
                        $total += $grade->grade;
                        $count++;
                    } else {
                        $grades[] = null;
                    }
                }

                $nb_lines = $pdf->getNumLines($subject->name, $subjectWidth);
                $cellHeight = 6 * $nb_lines;

                $x_start = $pdf->GetX();
                $y_start = $pdf->GetY();

                // Nomor
                $pdf->MultiCell($noWidth, $cellHeight, $no++, 1, 'C', false, 0, '', '', true, 0, false, true, $cellHeight, 'M');

                // Nama mata pelajaran
                $pdf->MultiCell($subjectWidth, $cellHeight, $subject->name, 1, 'L', false, 0, '', '', true, 0, false, true, $cellHeight, 'M');

                for ($i = 0; $i < 12; $i++) {
                    $value = isset($grades[$i]) ? $grades[$i] : null;
                    $pdf->MultiCell($semesterColWidth, $cellHeight, $value !== null ? $value : '', 1, 'C', false, 0, '', '', true, 0, false, true, $cellHeight, 'M');
                }

                // Rata-rata
                $average = $count > 0 ? round($total / $count, 0) : 0;
                $pdf->MultiCell($avgWidth, $cellHeight, $average > 0 ? $average : '', 1, 'C', false, 1, '', '', true, 0, false, true, $cellHeight, 'M');

                if ($average > 0) {
                    $allAverages[] = $average;
                }
            }
        }

        // Baris Jumlah
        $pdf->SetFont('times', 'B', 10);
        $pdf->SetFillColor(255, 255, 255);
        $jumlahWidth = $noWidth + $subjectWidth + ($semesterColWidth * 12);
        $pdf->Cell($jumlahWidth, 6, 'Jumlah', 1, 0, 'C', true);
        $totalAverage = array_sum($allAverages);
        $pdf->Cell($avgWidth, 6, $totalAverage, 1, 1, 'C', true);

        $pdf->Ln(10);

        // Tanda tangan
        $pdf->SetFont('times', '', 11);
        $pdf->Cell(100, 5, '', 0, 0, 'C');
        $pdf->Cell(80, 5, 'Gresik, ' . $this->formatIndonesianDate(now()), 0, 1, 'C');

        $pdf->Cell(100, 5, '', 0, 0, 'C');
        $pdf->Cell(80, 5, 'Kepala Madrasah', 0, 1, 'C');

        $pdf->Ln(20);

        $pdf->Cell(100, 5, '', 0, 0, 'C');
        $pdf->Cell(80, 5, '( FU\'AD, M.Pd.I )', 0, 1, 'C');

        $filename = 'Rekap_' . str_replace([' ', '.', ','], '_', $student->user->name) . '_' . now()->format('Y-m-d') . '.pdf';

        return response($pdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    private function numberToRoman($number)
    {
        $map = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI'
        ];
        return $map[$number] ?? $number;
    }
}
