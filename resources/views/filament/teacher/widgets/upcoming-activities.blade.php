<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Ringkasan Minggu Ini ({{ $this->weekStart }} - {{ $this->weekEnd }})
        </x-slot>

        <div style="display: grid; gap: 1.5rem; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
            <!-- Statistik Kehadiran -->
            <div class="rounded-lg border p-6" style="background-color: var(--filament-card-background); border-color: var(--filament-border-color);">
                <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: var(--filament-text-color);">
                    📊 Statistik Kehadiran
                </h3>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--filament-gray-400);">✅ Hadir</span>
                        <span style="font-weight: 600; color: rgb(34, 197, 94);">{{ $this->attendanceStats['Hadir'] }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--filament-gray-400);">🤒 Sakit</span>
                        <span style="font-weight: 600; color: rgb(250, 204, 21);">{{ $this->attendanceStats['Sakit'] }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--filament-gray-400);">📝 Izin</span>
                        <span style="font-weight: 600; color: rgb(59, 130, 246);">{{ $this->attendanceStats['Izin'] }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--filament-gray-400);">❌ Alpha</span>
                        <span style="font-weight: 600; color: rgb(239, 68, 68);">{{ $this->attendanceStats['Alpha'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Daftar Kelas -->
            <div class="rounded-lg border p-6" style="background-color: var(--filament-card-background); border-color: var(--filament-border-color);">
                <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: var(--filament-text-color);">
                    🏫 Daftar Kelas
                </h3>
                <div style="display: flex; flex-direction: column; gap: 0.75rem; max-height: 200px; overflow-y: auto;">
                    @forelse($this->classes as $class)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem; border-radius: 0.375rem; background-color: var(--filament-gray-50);">
                            <span style="font-weight: 500; color: var(--filament-text-color);">{{ $class->name }}</span>
                            <span style="padding: 0.25rem 0.75rem; border-radius: 9999px; background-color: rgba(34, 197, 94, 0.1); color: rgb(34, 197, 94); font-size: 0.875rem; font-weight: 500;">
                                {{ $class->students_count }} siswa
                            </span>
                        </div>
                    @empty
                        <p style="text-align: center; color: var(--filament-gray-400); padding: 1rem;">Belum ada kelas</p>
                    @endforelse
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
