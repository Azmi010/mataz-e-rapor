<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Student Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-semibold">{{ $student->user->name }}</h3>
                    <div class="mt-1 text-sm text-gray-600">
                        <p>NIS: {{ $student->nis }}</p>
                        <p>Kelas: {{ $class->name }}</p>
                        <p>Semester: {{ $semester->academicYear->name }} - Semester {{ $semester->semester_type }}</p>
                        @if($student->wali)
                            <p>Wali: {{ $student->wali }}</p>
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <a 
                        href="{{ route('filament.teacher.pages.grade-management') }}"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                        ← Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Attendance Summary -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Rekapitulasi Kehadiran</h3>
            
            <div class="grid grid-cols-4 gap-4">
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <div class="text-2xl font-bold text-green-600">{{ $attendanceSummary['Hadir'] }}</div>
                    <div class="text-sm text-green-600">Hadir</div>
                </div>
                <div class="text-center p-4 bg-yellow-50 rounded-lg">
                    <div class="text-2xl font-bold text-yellow-600">{{ $attendanceSummary['Sakit'] }}</div>
                    <div class="text-sm text-yellow-600">Sakit</div>
                </div>
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600">{{ $attendanceSummary['Izin'] }}</div>
                    <div class="text-sm text-blue-600">Izin</div>
                </div>
                <div class="text-center p-4 bg-red-50 rounded-lg">
                    <div class="text-2xl font-bold text-red-600">{{ $attendanceSummary['Alpha'] }}</div>
                    <div class="text-sm text-red-600">Alpha</div>
                </div>
            </div>
        </div>

        <!-- Grading Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Input Nilai</h3>
            
            <form wire:submit="save">
                {{ $this->form }}
                
                <div class="mt-6 flex justify-end">
                    <button 
                        type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                        Simpan Semua
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>
