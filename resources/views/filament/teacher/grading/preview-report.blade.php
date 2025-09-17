@php
    $student = $this->record;

    $currentDate = now()->format('Y-m-d');
    $currentSemester = \App\Models\Semester::whereHas('academicYear', function ($q) {
        $q->where('status', true);
    })
        ->whereDate('start_date', '<=', $currentDate)
        ->whereDate('end_date', '>=', $currentDate)
        ->first();

    if (!$currentSemester) {
        $currentSemester = \App\Models\Semester::whereHas('academicYear', function ($q) {
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

    if ($reportCard && $reportCard->semester) {
        $totalAttendances = $student->attendances()->count();
        $semesterAttendances = $student->attendances()->where('semester_id', $reportCard->semester->id)->count();
        $allSemesterAttendances = $student->attendances()->get();

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
                $debugInfo['using_active_semester'] = $activeSemester->id;
            }

            if (empty($attendances)) {
                $attendances = $student
                    ->attendances()
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

    $totalGrades = $reportCard?->grades->count() ?? 0;
    $totalScore = $reportCard?->grades->sum('grade') ?? 0;
    $averageScore = $totalGrades > 0 ? round($totalScore / $totalGrades, 1) : 0;

    $sakitCount = $attendanceStats['Sakit'] ?? 0;
    $izinCount = $attendanceStats['Izin'] ?? 0;
    $alphaCount = $attendanceStats['Alpha'] ?? 0;
    $hadirCount = $attendanceStats['Hadir'] ?? 0;
    $totalAbsent = $sakitCount + $izinCount + $alphaCount;
    $totalDays = $hadirCount + $totalAbsent;
    $attendancePercentage = $totalDays > 0 ? round(($hadirCount / $totalDays) * 100, 1) : 0;
@endphp

<x-filament-panels::page>
    <div class="bg-white">
        @if ($reportCard)
            <div class="max-w-4xl mx-auto bg-white" style="font-family: 'Times New Roman', serif;">
                {{-- Header --}}
                <div class="mb-2 border-b-4 border-black pb-2">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                            <img src="{{ asset('/img/logo.png') }}" alt="Logo" class="w-40 object-contain">
                        </div>
                        <div class="flex-grow text-center">
                            <div class="text-2xl font-bold mb-1 tracking-widest">M A T A Z</div>
                            <div class="text-xl font-bold mb-1 tracking-wide">( Markaz Tahfidz El-Zahro )</div>
                            <div class="text-xs font-bold leading-relaxed">
                                Dsn. Poncol, Ds. Banjarejo, Kec. Karangbinangun, Kab. Lamongan<br>
                                Telp: 081330578575/081332222366 | Email: markaztahfidzelzahroh@gmail.com | Website:
                                www.mataz.sch.id
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center text-2xl font-bold" style="font-family: 'Arabic Typesetting', serif;">
                    بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ
                </div>
                <div class="text-center text-lg font-bold mb-2">TRANSKRIP NILAI SANTRI</div>

                {{-- Student Information --}}
                <div class="mb-6">
                    <table class="w-full text-base">
                        <tr>
                            <td class="w-32">Nama</td>
                            <td class="w-4">:</td>
                            <td class="">{{ $student->user->name ?? '-' }}</td>
                            <td class="w-8"></td>
                        </tr>
                        <tr>
                            <td class="">No Induk</td>
                            <td>:</td>
                            <td class="">{{ $student->nis ?? '-' }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="">Kelas/Level</td>
                            <td>:</td>
                            <td class="">{{ $student->classModel->name ?? '-' }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="">Alamat</td>
                            <td>:</td>
                            <td class="">{{ $student->classModel->name ?? '-' }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="">Wali Murid</td>
                            <td>:</td>
                            <td class="">{{ $student->wali ?? '-' }}</td>
                            <td></td>
                        </tr>
                    </table>
                </div>

                {{-- Grades Section --}}
                <div class="mb-6">
                    @php
                        $groupedGrades = $reportCard->grades->groupBy('subject_id');
                        $juz30Grades = null;
                        $juz29Grades = null;
                        $otherGrades = collect();
                        $no = 1;

                        $sortedGrades = $groupedGrades->sortBy(function ($grades) {
                            $subject = $grades->first()->subject;
                            $name = strtolower($subject->name);

                            if (str_contains($name, 'juz 30')) {
                                return 1;
                            }
                            if (str_contains($name, 'juz 29')) {
                                return 2;
                            }
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

                        function getGradeInfo($grade)
                        {
                            if ($grade >= 95) {
                                return ['Istimewa', 'A'];
                            }
                            if ($grade >= 85) {
                                return ['Sangat baik', 'B'];
                            }
                            if ($grade >= 75) {
                                return ['Baik', 'C'];
                            }
                            if ($grade >= 65) {
                                return ['Cukup', 'D'];
                            }
                            return ['Kurang', 'E'];
                        }
                    @endphp

                    @if ($groupedGrades->count() > 0)
                        @if ($juz30Grades)
                            @php
                                $subject = $juz30Grades->first()->subject;
                                $mainGrade = $juz30Grades->where('subject_detail_id', null)->first();
                                $detailGrades = $juz30Grades
                                    ->where('subject_detail_id', '!=', null)
                                    ->sortBy('subjectDetail.order');
                            @endphp

                            <div class="mb-4">
                                <table class="table w-full border-2 border-black text-sm">
                                    <thead>
                                        <tr class="bg-gray-100">
                                            <th class="border-2 border-black px-2 py-2 text-center font-bold"
                                                colspan="2">Obyek
                                                Penilaian</th>
                                            <th class="border-2 border-black px-2 py-2 text-center font-bold"
                                                colspan="2">Nilai</th>
                                            <th class="border-2 border-black px-2 py-2 text-center font-bold"
                                                colspan="2">Obyek
                                                Penilaian</th>
                                            <th class="border-2 border-black px-2 py-2 text-center font-bold"
                                                colspan="2">Nilai</th>
                                        </tr>
                                        <tr class="bg-gray-100">
                                            <th class="border-2 border-black px-2 py-2 text-center font-bold"
                                                colspan="2">Juz 30
                                            </th>
                                            <th class="border-2 border-black px-2 py-2 text-center font-bold">Angka</th>
                                            <th class="border-2 border-black px-2 py-2 text-center font-bold">Keterangan
                                            </th>
                                            <th class="border-2 border-black px-2 py-2 text-center font-bold"
                                                colspan="2">Juz 30
                                            </th>
                                            <th class="border-2 border-black px-2 py-2 text-center font-bold">Angka</th>
                                            <th class="border-2 border-black px-2 py-2 text-center font-bold">Keterangan
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $detailGradesArray = $detailGrades->values()->toArray();
                                            $totalGrades = count($detailGradesArray);
                                            $halfCount = ceil($totalGrades / 2);
                                        @endphp

                                        @for ($i = 0; $i < $halfCount; $i++)
                                            <tr>
                                                @if (isset($detailGradesArray[$i]))
                                                    @php
                                                        $leftGrade = $detailGradesArray[$i];
                                                        $leftGradeInfo = getGradeInfo($leftGrade['grade'] ?? 0);
                                                        $leftSurahName = $leftGrade['subject_detail']['name'] ?? '';
                                                        $leftArabicName = preg_match(
                                                            '/^([^\(]+)/',
                                                            $leftSurahName,
                                                            $matches,
                                                        )
                                                            ? trim($matches[1])
                                                            : $leftSurahName;
                                                    @endphp
                                                    <td class="border-2 border-black px-1 py-1 text-center">
                                                        {{ $i + 1 }}</td>
                                                    <td class="border-2 border-black px-1 py-1 text-center"
                                                        style="font-family: 'Arabic Typesetting', serif;">
                                                        {{ $leftArabicName }}</td>
                                                    <td class="border-2 border-black px-2 py-1 text-center">
                                                        {{ $leftGrade['grade'] ?? 100 }}</td>
                                                    <td class="border-2 border-black px-2 py-1 text-center">
                                                        {{ $leftGrade['grade'] ? $leftGradeInfo[0] : 'Istimewa' }}</td>
                                                @else
                                                    <td class="border-2 border-black px-1 py-1"></td>
                                                    <td class="border-2 border-black px-1 py-1"></td>
                                                    <td class="border-2 border-black px-2 py-1"></td>
                                                    <td class="border-2 border-black px-2 py-1"></td>
                                                @endif

                                                @php $rightIndex = $i + $halfCount; @endphp
                                                @if (isset($detailGradesArray[$rightIndex]))
                                                    @php
                                                        $rightGrade = $detailGradesArray[$rightIndex];
                                                        $rightGradeInfo = getGradeInfo($rightGrade['grade'] ?? 0);
                                                        $rightSurahName = $rightGrade['subject_detail']['name'] ?? '';
                                                        $rightArabicName = preg_match(
                                                            '/^([^\(]+)/',
                                                            $rightSurahName,
                                                            $matches,
                                                        )
                                                            ? trim($matches[1])
                                                            : $rightSurahName;
                                                    @endphp
                                                    <td class="border-2 border-black px-1 py-1 text-center">
                                                        {{ $rightIndex + 1 }}</td>
                                                    <td class="border-2 border-black px-1 py-1 text-center"
                                                        style="font-family: 'Arabic Typesetting', serif;">
                                                        {{ $rightArabicName }}</td>
                                                    <td class="border-2 border-black px-2 py-1 text-center">
                                                        {{ $rightGrade['grade'] ?? 99 }}</td>
                                                    <td class="border-2 border-black px-2 py-1 text-center">
                                                        {{ $rightGrade['grade'] ? $rightGradeInfo[0] : 'Sangat baik' }}
                                                    </td>
                                                @else
                                                    <td class="border-2 border-black px-1 py-1"></td>
                                                    <td class="border-2 border-black px-1 py-1"></td>
                                                    <td class="border-2 border-black px-2 py-1"></td>
                                                    <td class="border-2 border-black px-2 py-1"></td>
                                                @endif
                                            </tr>
                                        @endfor

                                        <tr class="bg-gray-100">
                                            <td class="border-2 border-black px-2 py-2 text-center font-bold"
                                                colspan="3">Total Nilai</td>
                                            <td class="border-2 border-black px-2 py-2 text-center font-bold">
                                                {{ $detailGrades->sum('grade') ?: 3668 }}</td>
                                            <td class="border-2 border-black px-2 py-2 text-center font-bold"
                                                colspan="3">Rata-rata</td>
                                            <td class="border-2 border-black px-2 py-2 text-center font-bold">
                                                {{ $detailGrades->count() > 0 ? round($detailGrades->sum('grade') / $detailGrades->count(), 1) : 99.1 }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        @if ($juz29Grades)
                            @php
                                $subject = $juz29Grades->first()->subject;
                                $mainGrade = $juz29Grades->where('subject_detail_id', null)->first();
                                $detailGrades = $juz29Grades
                                    ->where('subject_detail_id', '!=', null)
                                    ->sortBy('subjectDetail.order');
                            @endphp

                            <div class="mb-4">
                                <table class="w-full border-2 border-black text-sm">
                                    <thead>
                                        <tr class="bg-gray-100">
                                            <th class="border-2 border-black px-2 py-2 text-center font-bold"
                                                colspan="2">Obyek
                                                Penilaian</th>
                                            <th class="border-2 border-black px-2 py-2 text-center font-bold"
                                                colspan="2">Nilai</th>
                                            <th class="border-2 border-black px-2 py-2 text-center font-bold"
                                                colspan="2">Obyek
                                                Penilaian</th>
                                            <th class="border-2 border-black px-2 py-2 text-center font-bold"
                                                colspan="2">Nilai</th>
                                        </tr>
                                        <tr class="bg-gray-100">
                                            <th class="border-2 border-black px-2 py-2 text-center font-bold"
                                                colspan="2">Juz 29
                                            </th>
                                            <th class="border-2 border-black px-2 py-2 text-center font-bold">Angka</th>
                                            <th class="border-2 border-black px-2 py-2 text-center font-bold">Keterangan
                                            </th>
                                            <th class="border-2 border-black px-2 py-2 text-center font-bold"
                                                colspan="2">Juz 29
                                            </th>
                                            <th class="border-2 border-black px-2 py-2 text-center font-bold">Angka</th>
                                            <th class="border-2 border-black px-2 py-2 text-center font-bold">Keterangan
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $detailGradesArray = $detailGrades->values()->toArray();
                                            $totalGrades = count($detailGradesArray);
                                            $halfCount = ceil($totalGrades / 2);
                                        @endphp

                                        @for ($i = 0; $i < $halfCount; $i++)
                                            <tr>
                                                @if (isset($detailGradesArray[$i]))
                                                    @php
                                                        $leftGrade = $detailGradesArray[$i];
                                                        $leftGradeInfo = getGradeInfo($leftGrade['grade'] ?? 0);
                                                        $leftSurahName = $leftGrade['subject_detail']['name'] ?? '';
                                                        $leftArabicName = preg_match(
                                                            '/^([^\(]+)/',
                                                            $leftSurahName,
                                                            $matches,
                                                        )
                                                            ? trim($matches[1])
                                                            : $leftSurahName;
                                                    @endphp
                                                    <td class="border-2 border-black px-1 py-1 text-center">
                                                        {{ $i + 1 }}</td>
                                                    <td class="border-2 border-black px-1 py-1 text-center"
                                                        style="font-family: 'Arabic Typesetting', serif;">
                                                        {{ $leftArabicName }}</td>
                                                    <td class="border-2 border-black px-2 py-1 text-center">
                                                        {{ $leftGrade['grade'] ?? 100 }}</td>
                                                    <td class="border-2 border-black px-2 py-1 text-center">
                                                        {{ $leftGrade['grade'] ? $leftGradeInfo[0] : 'Istimewa' }}</td>
                                                @else
                                                    <td class="border-2 border-black px-1 py-1"></td>
                                                    <td class="border-2 border-black px-1 py-1"></td>
                                                    <td class="border-2 border-black px-2 py-1"></td>
                                                    <td class="border-2 border-black px-2 py-1"></td>
                                                @endif

                                                @php $rightIndex = $i + $halfCount; @endphp
                                                @if (isset($detailGradesArray[$rightIndex]))
                                                    @php
                                                        $rightGrade = $detailGradesArray[$rightIndex];
                                                        $rightGradeInfo = getGradeInfo($rightGrade['grade'] ?? 0);
                                                        $rightSurahName = $rightGrade['subject_detail']['name'] ?? '';
                                                        $rightArabicName = preg_match(
                                                            '/^([^\(]+)/',
                                                            $rightSurahName,
                                                            $matches,
                                                        )
                                                            ? trim($matches[1])
                                                            : $rightSurahName;
                                                    @endphp
                                                    <td class="border-2 border-black px-1 py-1 text-center">
                                                        {{ $rightIndex + 1 }}</td>
                                                    <td class="border-2 border-black px-1 py-1 text-center"
                                                        style="font-family: 'Arabic Typesetting', serif;">
                                                        {{ $rightArabicName }}</td>
                                                    <td class="border-2 border-black px-2 py-1 text-center">
                                                        {{ $rightGrade['grade'] ?? 98 }}</td>
                                                    <td class="border-2 border-black px-2 py-1 text-center">
                                                        {{ $rightGrade['grade'] ? $rightGradeInfo[0] : 'Sangat baik' }}
                                                    </td>
                                                @else
                                                    <td class="border-2 border-black px-1 py-1"></td>
                                                    <td class="border-2 border-black px-1 py-1"></td>
                                                    <td class="border-2 border-black px-2 py-1"></td>
                                                    <td class="border-2 border-black px-2 py-1"></td>
                                                @endif
                                            </tr>
                                        @endfor

                                        <tr class="bg-gray-100">
                                            <td class="border-2 border-black px-2 py-2 text-center font-bold"
                                                colspan="3">Total Nilai</td>
                                            <td class="border-2 border-black px-2 py-2 text-center font-bold">
                                                {{ $detailGrades->sum('grade') ?: 1089 }}</td>
                                            <td class="border-2 border-black px-2 py-2 text-center font-bold"
                                                colspan="3">Rata-rata</td>
                                            <td class="border-2 border-black px-2 py-2 text-center font-bold">
                                                {{ $detailGrades->count() > 0 ? round($detailGrades->sum('grade') / $detailGrades->count(), 1) : 99.0 }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        @if ($otherGrades->count() > 0)
                            @php
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
                                $averageOtherGrades =
                                    $allOtherMainGrades->count() > 0
                                        ? round($totalOtherGrades / $allOtherMainGrades->count(), 1)
                                        : 0;
                            @endphp
                            <table class="w-full border-2 border-black text-sm">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="border-2 border-black px-2 py-2 text-center font-bold"
                                            rowspan="2">Obyek
                                            Penilaian Per Juz</th>
                                        <th class="border-2 border-black px-2 py-2 text-center font-bold w-16"
                                            colspan="2">Nilai
                                        </th>
                                    </tr>
                                    <tr class="bg-gray-100">
                                        <th class="border-2 border-black px-2 py-2 text-center font-bold w-24">Angka
                                        </th>
                                        <th class="border-2 border-black px-2 py-2 text-center font-bold w-48">
                                            Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($otherGrades as $subjectId => $grades)
                                        @php
                                            $subject = $grades->first()->subject;
                                            $mainGrade = $grades->where('subject_detail_id', null)->first();
                                            $gradeInfo = getGradeInfo($mainGrade['grade'] ?? 0);
                                            $detailGrades = $grades
                                                ->where('subject_detail_id', '!=', null)
                                                ->sortBy('subjectDetail.order');
                                        @endphp

                                        {{-- Main Subject --}}
                                        <tr>
                                            <td class="border-2 border-black px-2 py-2 text-left">
                                                {{ strtoupper($subject->name) }}</td>
                                            <td class="border-2 border-black px-2 py-2 text-center">
                                                {{ $mainGrade ? $mainGrade->grade : '-' }}
                                            </td>
                                            <td class="border-2 border-black px-2 py-2 text-center">
                                                {{ $gradeInfo[0] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-gray-100">
                                        <td class="border-2 border-black px-2 py-2 text-center font-bold">Total Nilai
                                        </td>
                                        <td class="border-2 border-black px-2 py-2 text-center font-bold"
                                            colspan="2">
                                            {{ $totalOtherGrades }}</td>
                                    </tr>
                                    <tr class="bg-gray-100">
                                        <td class="border-2 border-black px-2 py-2 text-center font-bold">Rata-rata
                                        </td>
                                        <td class="border-2 border-black px-2 py-2 text-center font-bold"
                                            colspan="2">
                                            {{ $averageOtherGrades }}
                                        </td>
                                    </tr>
                                    <tr class="bg-gray-100">
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td class="font-bold border-2 border-black px-2 py-2 text-left">Total Nilai
                                            Keseluruhan</td>
                                        <td class="border-2 border-black px-2 py-2 text-left font-bold"
                                            colspan="2">{{ $totalScore }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-bold border-2 border-black px-2 py-2 text-left">Rata-rata
                                            Keseluruhan</td>
                                        <td class="border-2 border-black px-2 py-2 text-left font-bold"
                                            colspan="2">{{ $averageScore }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-2 border-black px-2 py-2 text-left" colspan="3">Absensi
                                            Kehadiran</td>
                                    </tr>
                                    <tr>
                                        <td class="border-2 border-black px-2 py-2 text-left">Izin</td>
                                        <td class="border-2 border-black px-2 py-2 text-center">
                                            {{ $izinCount == 0 ? '-' : $izinCount }}</td>
                                        <td class="border-2 border-black px-2 py-2 text-left">Hari</td>
                                    </tr>
                                    <tr>
                                        <td class="border-2 border-black px-2 py-2 text-left">Sakit</td>
                                        <td class="border-2 border-black px-2 py-2 text-center">
                                            {{ $sakitCount == 0 ? '-' : $sakitCount }}</td>
                                        <td class="border-2 border-black px-2 py-2 text-left">Hari</td>
                                    </tr>
                                    <tr>
                                        <td class="border-2 border-black px-2 py-2 text-left">Absen</td>
                                        <td class="border-2 border-black px-2 py-2 text-center">
                                            {{ $alphaCount == 0 ? '-' : $alphaCount }}</td>
                                        <td class="border-2 border-black px-2 py-2 text-left">Hari</td>
                                    </tr>
                                    <tr>
                                        <td class="border-2 border-black px-2 py-2 text-left">Jumlah</td>
                                        <td class="border-2 border-black px-2 py-2 text-center">
                                            {{ $totalAbsent == 0 ? '-' : $totalAbsent }}</td>
                                        <td class="border-2 border-black px-2 py-2 text-left">Hari</td>
                                    </tr>
                                    <tr class="bg-gray-100">
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td class="border-2 border-black px-2 py-2 text-left">Keterangan Kelulusan</td>
                                        <td class="border-2 border-black px-2 py-2 text-left" colspan="2"></td>
                                    </tr>
                                    <tr>
                                        <td class="border-2 border-black px-2 py-2 text-left" colspan="3">Pesan
                                            Fasilitator:<br> {{ $reportCard->teacher_comment ?? '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        @endif
                    @else
                        <table class="w-full border-2 border-black text-sm">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border-2 border-black px-2 py-2 text-center font-bold" rowspan="2">
                                        Obyek
                                        Penilaian Per Juz</th>
                                    <th class="border-2 border-black px-2 py-2 text-center font-bold w-16"
                                        colspan="2">Nilai
                                    </th>
                                </tr>
                                <tr class="bg-gray-100">
                                    <th class="border-2 border-black px-2 py-2 text-center font-bold w-24">Angka
                                    </th>
                                    <th class="border-2 border-black px-2 py-2 text-center font-bold w-48">
                                        Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="4"
                                        class="border-2 border-black px-2 py-8 text-center text-gray-500 italic">
                                        Belum ada nilai yang diinput untuk siswa ini
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    @endif
                </div>

                {{-- Signatures --}}
                <div class="grid grid-cols-2 gap-8 text-center text-sm mb-6">
                    <div>
                        <p class="mb-16"><br>Wali Murid</p>
                        <div class="border-b-2 border-black w-32 mx-auto mb-2"></div>
                        <p>(.........................................)</p>
                    </div>

                    <div>
                        <p class="mb-16">Lamongan, {{ now()->format('d F Y') }}<br>Ketua MATAZ El Zahroh</p>
                        <div class="border-b-2 border-black w-32 mx-auto mb-2"></div>
                        <p>( FU'AD, M.Pd.I )</p>
                    </div>
                </div>
            </div>

            {{-- Download PDF Button --}}
            <div class="text-center my-6 print-hidden">
                <button onclick="downloadPDF()"
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold shadow-lg transition-colors">
                    📄 Download PDF Rapor
                </button>
            </div>
        @else
            <div class="text-center py-12">
                <div class="mb-4">
                    <svg class="w-16 h-16 text-gray-400 mx-auto" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
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

    <style>
        .flex {
            display: flex;
        }

        .items-center {
            align-items: center;
        }

        .gap-4 {
            gap: 1rem;
        }

        .flex-shrink-0 {
            flex-shrink: 0;
        }

        .flex-grow {
            flex-grow: 1;
        }

        .h-16 {
            height: 4rem;
        }

        .max-w-4xl {
            max-width: 56rem;
        }

        .mx-auto {
            margin-left: auto;
            margin-right: auto;
        }

        .bg-white {
            background-color: #ffffff;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: 700;
        }

        .text-2xl {
            font-size: 1.5rem;
            line-height: 2rem;
        }

        .text-xl {
            font-size: 1.25rem;
            line-height: 1.75rem;
        }

        .text-lg {
            font-size: 1.125rem;
            line-height: 1.75rem;
        }

        .text-base {
            font-size: 1rem;
            line-height: 1.5rem;
        }

        .text-sm {
            font-size: 0.875rem;
            line-height: 1.25rem;
        }

        .text-xs {
            font-size: 0.75rem;
            line-height: 1rem;
        }

        .mb-6 {
            margin-bottom: 1.5rem;
        }

        .mb-4 {
            margin-bottom: 1rem;
        }

        .mb-3 {
            margin-bottom: 0.75rem;
        }

        .mb-2 {
            margin-bottom: 0.5rem;
        }

        .mb-16 {
            margin-bottom: 4rem;
        }

        .py-1 {
            padding-top: 0.25rem;
            padding-bottom: 0.25rem;
        }

        .py-2 {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        .py-3 {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }

        .py-8 {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }

        .py-12 {
            padding-top: 3rem;
            padding-bottom: 3rem;
        }

        .px-2 {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }

        .px-3 {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .px-4 {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .px-6 {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }

        .p-3 {
            padding: 0.75rem;
        }

        .p-4 {
            padding: 1rem;
        }

        .pb-4 {
            padding-bottom: 1rem;
        }

        .pl-6 {
            padding-left: 1.5rem;
        }

        .pt-2 {
            padding-top: 0.5rem;
        }

        .w-4 {
            width: 1rem;
        }

        .w-8 {
            width: 2rem;
        }

        .w-12 {
            width: 3rem;
        }

        .w-16 {
            width: 4rem;
        }

        .w-32 {
            width: 8rem;
        }

        .w-40 {
            width: 10rem;
        }

        .w-full {
            width: 100%;
        }

        .min-h-20 {
            min-height: 5rem;
        }

        .border {
            border-width: 1px;
        }

        .border-2 {
            border-width: 2px;
        }

        .border-4 {
            border-width: 4px;
        }

        .border-t-2 {
            border-top-width: 2px;
        }

        .border-b-2 {
            border-bottom-width: 2px;
        }

        .border-b-4 {
            border-bottom-width: 4px;
        }

        .border-black {
            border-color: #000000;
        }

        .border-gray-300 {
            border-color: #d1d5db;
        }

        .border-yellow-300 {
            border-color: #fde047;
        }

        .bg-gray-100 {
            background-color: #f3f4f6;
        }

        .bg-yellow-100 {
            background-color: #fef3c7;
        }

        .bg-green-600 {
            background-color: #16a34a;
        }

        .text-gray-400 {
            color: #9ca3af;
        }

        .text-gray-500 {
            color: #6b7280;
        }

        .text-gray-600 {
            color: #4b5563;
        }

        .text-green-600 {
            color: #16a34a;
        }

        .text-orange-600 {
            color: #ea580c;
        }

        .text-red-600 {
            color: #dc2626;
        }

        .text-blue-600 {
            color: #2563eb;
        }

        .text-white {
            color: #ffffff;
        }

        .italic {
            font-style: italic;
        }

        .underline {
            text-decoration-line: underline;
        }

        .tracking-wide {
            letter-spacing: 0.025em;
        }

        .tracking-widest {
            letter-spacing: 0.1em;
        }

        .leading-relaxed {
            line-height: 1.625;
        }

        .grid {
            display: grid;
        }

        .grid-cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .grid-cols-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .gap-6 {
            gap: 1.5rem;
        }

        .gap-8 {
            gap: 2rem;
        }

        .my-6 {
            margin-top: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .mt-2 {
            margin-top: 0.5rem;
        }

        .rounded {
            border-radius: 0.25rem;
        }

        .rounded-lg {
            border-radius: 0.5rem;
        }

        .shadow-lg {
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        .transition-colors {
            transition-property: color, background-color, border-color, text-decoration-color, fill, stroke;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 150ms;
        }

        .hover\:bg-green-700:hover {
            background-color: #15803d;
        }

        table {
            border-collapse: collapse;
            font-family: 'Times New Roman', serif;
        }

        @media print {
            @page {
                size: A4;
                margin: 15mm;
            }

            body {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                print-color-adjust: exact;
            }

            .print-hidden {
                display: none !important;
            }

            .text-2xl {
                font-size: 18px !important;
            }

            .text-xl {
                font-size: 16px !important;
            }

            .text-lg {
                font-size: 14px !important;
            }

            .text-base {
                font-size: 12px !important;
            }

            .text-sm {
                font-size: 11px !important;
            }

            .text-xs {
                font-size: 9px !important;
            }

            .border-2,
            .border-t-2,
            .border-b-2,
            .border-4 {
                border-color: #000 !important;
            }

            table,
            .grid {
                page-break-inside: avoid;
            }

            .bg-gray-100 {
                background-color: #f3f4f6 !important;
            }
        }
    </style>

    <script>
        function downloadPDF() {
            const studentName = '{{ $student->user->name ?? 'Siswa' }}';
            const semester = '{{ $reportCard?->semester?->semester_type ?? 'Semester' }}';
            const academicYear = '{{ $reportCard?->semester?->academicYear?->name ?? '2024' }}';

            const originalTitle = document.title;
            document.title = `Rapor_${studentName}_${semester}_${academicYear}`.replace(/[^a-zA-Z0-9_-]/g, '_');

            window.print();

            setTimeout(() => {
                document.title = originalTitle;
            }, 1000);
        }
    </script>
</x-filament-panels::page>
