@php
    $semester = \App\Models\Semester::whereHas('academicYear', fn($q) => $q->where('status', true))
        ->orderByDesc('start_date')->first();
    $reportCard = $semester ? \App\Models\ReportCard::with('grades.subject','grades.subjectDetail')->firstWhere([
        'student_id' => $record->id,
        'semester_id' => $semester->id,
    ]) : null;
    $grouped = $reportCard?->grades->groupBy('subject_id') ?? collect();
@endphp
<x-filament::page>
    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-semibold">Rapor Sementara</h2>
            <div class="text-sm text-gray-600">Nama: <strong>{{ $record->user->name }}</strong> | NIS: {{ $record->nis }} | Kelas: {{ $record->classModel->name ?? '-' }}</div>
        </div>

        <div class="overflow-x-auto rounded border">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-3 py-2 text-left">Mata Pelajaran</th>
                        <th class="px-3 py-2 text-left">Komponen</th>
                        <th class="px-3 py-2 text-center">Nilai</th>
                        <th class="px-3 py-2 text-center">Huruf</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($grouped as $subjectId => $rows)
                    @php $rowCount = $rows->count(); @endphp
                    @foreach($rows as $i => $g)
                        <tr class="border-t">
                            @if($i === 0)
                                <td class="px-3 py-2 align-top font-medium" rowspan="{{ $rowCount }}">{{ $g->subject->name ?? '-' }}</td>
                            @endif
                            <td class="px-3 py-2">{{ $g->subjectDetail?->name ?? '-' }}</td>
                            <td class="px-3 py-2 text-center">{{ $g->grade ?? '-' }}</td>
                            <td class="px-3 py-2 text-center">{{ \App\Models\ReportCard::numericToLetter($g->grade) ?? '-' }}</td>
                        </tr>
                    @endforeach
                @empty
                    <tr><td colspan="4" class="px-3 py-4 text-center text-gray-500">Belum ada nilai.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="border rounded p-4 space-y-2">
                <h3 class="font-semibold">Rekap Kehadiran</h3>
                @php $att = $reportCard?->attendance_array ?? []; @endphp
                <ul class="text-sm space-y-1">
                    <li>Hadir: <strong>{{ $att['Hadir'] ?? 0 }}</strong></li>
                    <li>Sakit: <strong>{{ $att['Sakit'] ?? 0 }}</strong></li>
                    <li>Izin: <strong>{{ $att['Izin'] ?? 0 }}</strong></li>
                    <li>Alpha: <strong>{{ $att['Alpha'] ?? 0 }}</strong></li>
                </ul>
            </div>
            <div class="border rounded p-4 space-y-2">
                <h3 class="font-semibold">Catatan Wali Kelas</h3>
                <p class="text-sm whitespace-pre-line">{{ $reportCard?->teacher_comment ?? '—' }}</p>
            </div>
        </div>

        <div class="flex gap-3 print:hidden">
            <x-filament::button x-on:click="window.print()" icon="heroicon-o-printer">Print</x-filament::button>
        </div>
    </div>
</x-filament::page>
