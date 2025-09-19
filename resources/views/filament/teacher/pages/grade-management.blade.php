<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Form Filter -->
        {{ $this->form }}

        @if($semester_id && $class_id && count($students) > 0)
            <!-- Daftar Siswa -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold">Daftar Siswa</h3>
                    <p class="text-sm text-gray-600 mt-1">Klik "Isi Nilai" untuk memberikan nilai kepada siswa</p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIS</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Wali</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($students as $index => $student)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $student->user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $student->user->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $student->nis }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $student->wali ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <button 
                                            wire:click="openGradingForm({{ $student->id }})"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                                        >
                                            Isi Nilai
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif($semester_id && $class_id)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <p class="text-yellow-800">Tidak ada siswa dalam kelas yang dipilih.</p>
            </div>
        @else
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-blue-800">Pilih semester dan kelas untuk melihat daftar siswa.</p>
            </div>
        @endif
    </div>

    <!-- Modal for Grading -->
    @if($showGradingModal && $selectedStudent)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:ignore.self>
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeGradingModal"></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="w-full">
                                <!-- Modal Header -->
                                <div class="mb-6">
                                    <h3 class="text-lg font-medium text-gray-900">
                                        Form Penilaian - {{ $selectedStudent->user->name }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-600">
                                        NIS: {{ $selectedStudent->nis }}
                                    </p>
                                </div>

                                <!-- Student Info Card -->
                                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Informasi Siswa</h4>
                                            <p class="text-sm text-gray-600">Nama: {{ $selectedStudent->user->name }}</p>
                                            <p class="text-sm text-gray-600">NIS: {{ $selectedStudent->nis }}</p>
                                            <p class="text-sm text-gray-600">Wali: {{ $selectedStudent->wali ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900">Ringkasan Kehadiran</h4>
                                            <div class="grid grid-cols-2 gap-2 text-sm">
                                                <span class="text-green-600">Hadir: {{ $attendanceSummary['Hadir'] ?? 0 }}</span>
                                                <span class="text-yellow-600">Sakit: {{ $attendanceSummary['Sakit'] ?? 0 }}</span>
                                                <span class="text-blue-600">Izin: {{ $attendanceSummary['Izin'] ?? 0 }}</span>
                                                <span class="text-red-600">Alpha: {{ $attendanceSummary['Alpha'] ?? 0 }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Grading Form -->
                                <form wire:submit.prevent="saveGrades">
                                    <!-- Subjects and Grades -->
                                    <div class="mb-6">
                                        <h4 class="font-medium text-gray-900 mb-4">Nilai Mata Pelajaran</h4>
                                        <div class="space-y-4">
                                            @if($selectedStudentSubjects)
                                                @foreach($selectedStudentSubjects as $subject)
                                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center p-3 border rounded-lg">
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700">
                                                                {{ $subject->name }}
                                                            </label>
                                                            @if($subject->details && count($subject->details) > 0)
                                                                <p class="text-xs text-gray-500">
                                                                    {{ $subject->details->pluck('name')->implode(', ') }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <input 
                                                                type="number" 
                                                                min="0" 
                                                                max="100" 
                                                                step="0.1"
                                                                wire:model="gradingData.grade_{{ $subject->id }}"
                                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                                                placeholder="0-100"
                                                            >
                                                        </div>
                                                        <div class="text-sm text-gray-600">
                                                            @php
                                                                $gradeValue = $gradingData["grade_" . $subject->id] ?? 0;
                                                                if ($gradeValue >= 90) $desc = 'Sangat Baik';
                                                                elseif ($gradeValue >= 80) $desc = 'Baik';
                                                                elseif ($gradeValue >= 70) $desc = 'Cukup';
                                                                elseif ($gradeValue >= 60) $desc = 'Kurang';
                                                                else $desc = $gradeValue > 0 ? 'Sangat Kurang' : '-';
                                                            @endphp
                                                            {{ $desc }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Teacher Comment -->
                                    <div class="mb-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Catatan Guru
                                        </label>
                                        <textarea 
                                            wire:model="gradingData.teacher_comment"
                                            rows="3"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                            placeholder="Tuliskan catatan atau komentar untuk siswa..."
                                        ></textarea>
                                    </div>

                                    <!-- Form Actions -->
                                    <div class="flex justify-end space-x-3">
                                        <button 
                                            type="button"
                                            wire:click="closeGradingModal"
                                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                                        >
                                            Batal
                                        </button>
                                        <button 
                                            type="submit"
                                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                                        >
                                            Simpan Nilai
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
