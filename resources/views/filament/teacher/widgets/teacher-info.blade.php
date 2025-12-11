<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            👨‍🏫 Informasi Guru
        </x-slot>

        @php
            $teacherData = $this->teacherData;
        @endphp

        @if($teacherData)
            <div style="display: grid; gap: 1.5rem; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
                <!-- Profil Guru -->
                <div style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); border: none; color: white; box-shadow: 0 10px 15px -3px rgba(5, 150, 105, 0.2), 0 4px 6px -2px rgba(5, 150, 105, 0.15); border-radius: 1rem; padding: 1.5rem;">
                    <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">
                        {{ $teacherData['name'] }}
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.875rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="opacity: 0.9;">📋 NIP:</span>
                            <span style="font-weight: 500;">{{ $teacherData['nip'] }}</span>
                        </div>
                        @if($teacherData['homeroom_class'])
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span style="opacity: 0.9;">🏠 Wali Kelas:</span>
                                <span style="font-weight: 600; background-color: rgba(255,255,255,0.2); padding: 0.25rem 0.75rem; border-radius: 9999px;">
                                    {{ $teacherData['homeroom_class'] }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Mata Pelajaran -->
                <div style="background-color: var(--filament-card-background); border: 1px solid var(--filament-border-color); border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);">
                    <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: var(--filament-text-color);">
                        📚 Mata Pelajaran yang Diajar
                    </h3>
                    @if(count($teacherData['subjects']) > 0)
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            @foreach($teacherData['subjects'] as $subject)
                                <span style="padding: 0.5rem 1rem; border-radius: 0.5rem; background-color: rgba(59, 130, 246, 0.1); color: rgb(59, 130, 246); font-size: 0.875rem; font-weight: 500;">
                                    {{ $subject }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p style="color: var(--filament-gray-400); font-style: italic;">Belum ada mata pelajaran</p>
                    @endif
                </div>

                <!-- Tahun Ajaran & Semester -->
                <div style="background-color: var(--filament-card-background); border: 1px solid var(--filament-border-color); border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);">
                    <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: var(--filament-text-color);">
                        📅 Periode Akademik
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--filament-gray-400);">Tahun Ajaran</span>
                            <span style="font-weight: 600; color: var(--filament-text-color);">{{ $teacherData['academic_year'] }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--filament-gray-400);">Semester</span>
                            <span style="padding: 0.25rem 0.75rem; border-radius: 9999px; background-color: rgba(34, 197, 94, 0.1); color: rgb(34, 197, 94); font-size: 0.875rem; font-weight: 600;">
                                {{ $teacherData['semester'] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <p style="text-align: center; color: var(--filament-gray-400); padding: 2rem;">Data guru tidak ditemukan</p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
