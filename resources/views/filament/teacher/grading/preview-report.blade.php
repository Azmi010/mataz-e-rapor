@php
    $student = $this->record;

    $currentDate = now()->format('Y-m-d');
    $currentSemester = \App\Models\Semester::whereHas('academicYear', function($q) {
        $q->where('status', true);
    })
    ->whereDate('start_date', '<=', $currentDate)
    ->whereDate('end_date', '>=', $currentDate)
    ->first();

    if (!$currentSemester) {
        $currentSemester = \App\Models\Semester::whereHas('academicYear', function($q) {
            $q->where('status', true);
        })
        ->orderBy('semester_type', 'asc')
        ->first();
    }

    $reportCard = $student->reportCards()
        ->with(['semester.academicYear', 'grades.subject', 'grades.subjectDetail'])
        ->whereHas('semester', function($q) {
            $q->whereHas('academicYear', fn($aq) => $aq->where('status', true));
        })
        ->latest()
        ->first();

    if (!$reportCard && $currentSemester) {
        $reportCard = new \App\Models\ReportCard([
            'student_id' => $student->id,
            'semester_id' => $currentSemester->id,
            'teacher_comment' => '',
            'attendance' => '{}',
        ]);
        $reportCard->semester = $currentSemester;
        $reportCard->grades = collect();
    }

    $attendanceStats = [];
    $debugInfo = [];

    if ($reportCard && $reportCard->semester) {
        $totalAttendances = $student->attendances()->count();
        $semesterAttendances = $student->attendances()->where('semester_id', $reportCard->semester->id)->count();
        $allSemesterAttendances = $student->attendances()->get();

        $debugInfo = [
            'student_id' => $student->id,
            'current_date' => $currentDate,
            'expected_semester' => $currentSemester ? $currentSemester->semester_type : 'N/A',
            'actual_semester_used' => $reportCard->semester->semester_type,
            'semester_id' => $reportCard->semester->id,
            'semester_start' => $reportCard->semester->start_date,
            'semester_end' => $reportCard->semester->end_date,
            'has_grades' => $reportCard->grades->count() > 0,
            'total_grades' => $reportCard->grades->count(),
            'total_attendances' => $totalAttendances,
            'semester_attendances' => $semesterAttendances,
            'all_semester_ids' => $allSemesterAttendances->pluck('semester_id')->unique()->toArray(),
            'logic_used' => ($currentSemester && $reportCard->semester->id === $currentSemester->id) ? 'Current semester (by date)' : 'Fallback to semester with grades',
        ];

        $attendances = $student->attendances()
            ->where('semester_id', $reportCard->semester->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        if (empty($attendances)) {
            $activeSemester = \App\Models\Semester::whereHas('academicYear', fn($q) => $q->where('status', true))->first();
            if ($activeSemester && $activeSemester->id != $reportCard->semester->id) {
                $attendances = $student->attendances()
                    ->where('semester_id', $activeSemester->id)
                    ->selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();
                $debugInfo['using_active_semester'] = $activeSemester->id;
            }

            if (empty($attendances)) {
                $attendances = $student->attendances()
                    ->selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();
                $debugInfo['fallback_to_any_semester'] = true;
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
@endphp

<x-filament-panels::page>
    <div class="bg-white">
        @if($reportCard)
            <div class="report-card" style="font-family: 'Times New Roman', serif; max-width: 800px; margin: 0 auto; padding: 20px;">
                {{-- Header --}}
                <div class="header text-center mb-8">
                    <div class="flex justify-center items-center mb-4">
                        <div class="logo mr-6">
                            <div class="w-20 h-20 bg-green-600 rounded-full flex items-center justify-center text-white font-bold text-2xl shadow-lg">
                                M
                            </div>
                        </div>
                        <div class="text-left">
                            <h1 class="text-2xl font-bold text-green-700 mb-1 tracking-wide">MADRASAH TAKHASSUS AL-QUR'AN</h1>
                            <h2 class="text-xl font-bold text-green-700 mb-2 tracking-wider">MATAZ</h2>
                            <p class="text-sm text-gray-700 mb-1">Jl. Raya Cipanas No. 123, Cipanas, Cianjur 43253</p>
                            <p class="text-sm text-gray-700">Telp: (0263) 512345 | Email: info@mataz.sch.id | Website: www.mataz.sch.id</p>
                        </div>
                    </div>
                    <hr class="border-t-3 border-green-700 mb-6" style="border-width: 3px;">
                    <div class="bg-green-100 py-3 px-6 rounded-lg border-2 border-green-700">
                        <h3 class="text-xl font-bold text-green-800 mb-1">LAPORAN HASIL BELAJAR SANTRI</h3>
                        <h4 class="text-lg font-semibold text-green-700">SEMESTER {{ strtoupper($reportCard->semester->semester_type) }} TAHUN PELAJARAN {{ $reportCard->semester->academicYear->name }}</h4>
                    </div>
                </div>

                {{-- Student Info --}}
                <div class="student-info mb-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <table class="w-full text-sm">
                                <tr>
                                    <td class="font-semibold py-1 w-32">Nama Santri</td>
                                    <td class="py-1">: {{ $student->user->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-semibold py-1">NIS</td>
                                    <td class="py-1">: {{ $student->nis ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-semibold py-1">Kelas</td>
                                    <td class="py-1">: {{ $student->classModel->name ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div>
                            <table class="w-full text-sm">
                                <tr>
                                    <td class="font-semibold py-1 w-32">Semester</td>
                                    <td class="py-1">: {{ $reportCard->semester->semester_type ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-semibold py-1">Tahun Ajaran</td>
                                    <td class="py-1">: {{ $reportCard->semester->academicYear->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-semibold py-1">Wali Kelas</td>
                                    <td class="py-1">: {{ $student->wali ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Grades Table --}}
                <div class="grades-section mb-6">
                    <h4 class="text-base font-bold mb-3 text-green-700">HASIL BELAJAR</h4>
                    <table class="w-full border-2 border-black text-sm">
                        <thead>
                            <tr class="bg-green-100">
                                <th class="border border-black px-2 py-2 text-center font-bold w-12">NO</th>
                                <th class="border border-black px-2 py-2 text-center font-bold">MATA PELAJARAN</th>
                                <th class="border border-black px-2 py-2 text-center font-bold w-20">NILAI</th>
                                <th class="border border-black px-2 py-2 text-center font-bold w-16">HURUF</th>
                                <th class="border border-black px-2 py-2 text-center font-bold">KETERANGAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $no = 1;
                                $groupedGrades = $reportCard->grades->groupBy('subject_id');
                            @endphp

                            @if($groupedGrades->count() > 0)
                                @foreach($groupedGrades as $subjectId => $grades)
                                    @php
                                        $subject = $grades->first()->subject;
                                        $mainGrade = $grades->where('subject_detail_id', null)->first();
                                        $detailGrades = $grades->where('subject_detail_id', '!=', null)->sortBy('subjectDetail.order');
                                    @endphp

                                    {{-- Main Subject --}}
                                    <tr>
                                        <td class="border border-black px-2 py-2 text-center font-bold">{{ $no++ }}</td>
                                        <td class="border border-black px-2 py-2 font-bold">{{ strtoupper($subject->name) }}</td>
                                        <td class="border border-black px-2 py-2 text-center font-bold">
                                            {{ $mainGrade ? $mainGrade->grade : '-' }}
                                        </td>
                                        <td class="border border-black px-2 py-2 text-center font-bold">
                                            {{ $mainGrade ? App\Models\ReportCard::numericToLetter($mainGrade->grade) : '-' }}
                                        </td>
                                        <td class="border border-black px-2 py-2">
                                            {{ $mainGrade ? $mainGrade->description : '-' }}
                                        </td>
                                    </tr>

                                    {{-- Subject Details --}}
                                    @foreach($detailGrades as $detailGrade)
                                        <tr>
                                            <td class="border border-black px-2 py-2 text-center text-gray-400">{{ chr(96 + $loop->iteration) }}</td>
                                            <td class="border border-black px-2 py-2 pl-6 text-sm">
                                                • {{ $detailGrade->subjectDetail->name ?? 'Detail tidak ditemukan' }}
                                            </td>
                                            <td class="border border-black px-2 py-2 text-center">
                                                {{ $detailGrade->grade ?? '-' }}
                                            </td>
                                            <td class="border border-black px-2 py-2 text-center">
                                                {{ $detailGrade->grade ? App\Models\ReportCard::numericToLetter($detailGrade->grade) : '-' }}
                                            </td>
                                            <td class="border border-black px-2 py-2 text-sm">
                                                {{ $detailGrade->description ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="border border-black px-2 py-8 text-center text-gray-500">
                                        <em>Belum ada nilai yang diinput untuk siswa ini</em>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Attendance Section --}}
                <div class="attendance-section mb-6">
                    <h4 class="text-base font-bold mb-3 text-green-700">KEHADIRAN</h4>

                    {{-- Debug Info (temporary) --}}
                    @if(!empty($debugInfo))
                        <div class="bg-yellow-100 p-3 mb-4 rounded text-sm">
                            <strong>Debug Info:</strong><br>
                            Student ID: {{ $debugInfo['student_id'] ?? 'N/A' }}<br>
                            Current Date: {{ $debugInfo['current_date'] ?? 'N/A' }}<br>
                            <strong>Expected Semester (by date):</strong> {{ $debugInfo['expected_semester'] ?? 'N/A' }}<br>
                            <strong style="color: {{ ($debugInfo['expected_semester'] ?? '') == ($debugInfo['actual_semester_used'] ?? '') ? 'green' : 'orange' }}">
                                Actual Semester Used: {{ $debugInfo['actual_semester_used'] ?? 'N/A' }}
                            </strong><br>
                            Semester Period: {{ ($debugInfo['semester_start'] ?? 'N/A') . ' - ' . ($debugInfo['semester_end'] ?? 'N/A') }}<br>
                            <strong style="color: {{ ($debugInfo['has_grades'] ?? false) ? 'green' : 'red' }}">
                                Has Grades: {{ ($debugInfo['has_grades'] ?? false) ? 'YES' : 'NO' }} ({{ $debugInfo['total_grades'] ?? 0 }} grades)
                            </strong><br>
                            Total Attendances: {{ $debugInfo['total_attendances'] ?? 'N/A' }}<br>
                            Semester Attendances: {{ $debugInfo['semester_attendances'] ?? 'N/A' }}<br>
                            @if(isset($debugInfo['using_active_semester']))
                                <strong style="color: blue;">Using active semester: {{ $debugInfo['using_active_semester'] }}</strong><br>
                            @endif
                            @if(isset($debugInfo['fallback_to_any_semester']))
                                <strong style="color: red;">Using fallback: any semester data</strong><br>
                            @endif
                            Attendance Stats: {{ json_encode($attendanceStats) }}
                        </div>
                    @endif
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <table class="w-full border border-black text-sm">
                                <tr>
                                    <td class="border border-black px-3 py-2 font-semibold bg-green-100 w-40">Sakit</td>
                                    <td class="border border-black px-3 py-2 text-center">{{ $attendanceStats['Sakit'] ?? 0 }} hari</td>
                                </tr>
                                <tr>
                                    <td class="border border-black px-3 py-2 font-semibold bg-green-100">Izin</td>
                                    <td class="border border-black px-3 py-2 text-center">{{ $attendanceStats['Izin'] ?? 0 }} hari</td>
                                </tr>
                                <tr>
                                    <td class="border border-black px-3 py-2 font-semibold bg-green-100">Tanpa Keterangan</td>
                                    <td class="border border-black px-3 py-2 text-center">{{ $attendanceStats['Alpha'] ?? 0 }} hari</td>
                                </tr>
                            </table>
                        </div>
                        <div>
                            {{-- Additional Info or Statistics --}}
                            @php
                                $sakitCount = $attendanceStats['Sakit'] ?? 0;
                                $izinCount = $attendanceStats['Izin'] ?? 0;
                                $alphaCount = $attendanceStats['Alpha'] ?? 0;
                                $hadirCount = $attendanceStats['Hadir'] ?? 0;

                                $totalAbsent = $sakitCount + $izinCount + $alphaCount;
                                $totalDays = $hadirCount + $totalAbsent;
                                $attendancePercentage = $totalDays > 0 ? round(($hadirCount / $totalDays) * 100, 1) : 0;
                            @endphp
                            <div class="bg-green-50 p-4 rounded border border-green-200">
                                <h5 class="font-semibold text-green-700 mb-2">Statistik Kehadiran</h5>
                                <div class="space-y-1 text-sm">
                                    <p><span class="font-medium">Total Hari Efektif:</span> {{ $totalDays }} hari</p>
                                    <p><span class="font-medium">Hadir:</span> {{ $hadirCount }} hari</p>
                                    <p><span class="font-medium">Total Tidak Hadir:</span> {{ $totalAbsent }} hari</p>
                                    <hr class="my-2 border-green-200">
                                    <p class="font-semibold text-green-800">
                                        <span class="font-medium">Persentase Kehadiran:</span>
                                        <span class="text-lg">{{ $attendancePercentage }}%</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Teacher Comment --}}
                <div class="comment-section mb-6">
                    <h4 class="text-base font-bold mb-3 text-green-700">KOMENTAR WALI KELAS</h4>
                    <div class="border-2 border-black p-4 min-h-[100px]">
                        <p class="text-sm leading-relaxed">
                            {{ $reportCard->teacher_comment ?? 'Tidak ada komentar.' }}
                        </p>
                    </div>
                </div>

                {{-- Signatures --}}
                <div class="signatures-section mb-6">
                    <div class="grid grid-cols-3 gap-8 text-center">
                        <div>
                            <p class="text-sm font-semibold mb-12">Mengetahui,<br>Orang Tua/Wali</p>
                            <div class="border-b border-black w-32 mx-auto mb-2"></div>
                            <p class="text-sm">(.............................)</p>
                        </div>
                        <div>
                            <p class="text-sm font-semibold mb-12">Cipanas, {{ now()->format('d F Y') }}<br>Wali Kelas</p>
                            <div class="border-b border-black w-32 mx-auto mb-2"></div>
                            <p class="text-sm">{{ $student->wali ?? '(...........................)' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-semibold mb-12">Mengetahui,<br>Kepala Madrasah</p>
                            <div class="border-b border-black w-32 mx-auto mb-2"></div>
                            <p class="text-sm">(.............................)</p>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="footer text-center mt-8 pt-4 border-t border-gray-300">
                    <p class="text-xs text-gray-500">
                        Dicetak pada: {{ now()->format('d F Y H:i:s') }} |
                        Sistem Informasi Rapor MATAZ
                    </p>
                </div>
            </div>

            {{-- Print Button --}}
            <div class="text-center mt-6 no-print">
                <button onclick="window.print()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Cetak Rapor
                </button>
            </div>
        @else
            <div class="text-center py-12">
                <div class="mb-4">
                    <svg class="w-16 h-16 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-600 mb-2">Rapor Belum Tersedia</h3>
                <p class="text-gray-500">
                    Rapor untuk siswa <strong>{{ $student->user->name ?? 'Siswa' }}</strong>
                    pada semester aktif belum dibuat atau belum diisi.
                </p>
                <p class="text-sm text-gray-400 mt-2">
                    Silakan isi nilai terlebih dahulu melalui menu "Isi Nilai".
                </p>
            </div>
        @endif
    </div>

    {{-- Include Report Card CSS --}}
    @push('styles')
        @vite(['resources/css/report-card.css'])
    @endpush

    {{-- Additional Inline Styles --}}
    <style>
        .report-card table {
            border-collapse: collapse;
        }

        .report-card th,
        .report-card td {
            border: 1px solid #000 !important;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            @page {
                margin: 1.5cm;
                size: A4;
            }

            body {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                print-color-adjust: exact;
            }

            .report-card {
                font-size: 12px !important;
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .report-card .logo {
                background: #059669 !important;
            }

            .report-card .bg-green-100 {
                background-color: #dcfce7 !important;
            }

            .report-card .bg-green-50 {
                background-color: #f0fdf4 !important;
            }
        }
    </style>
</x-filament-panels::page>
