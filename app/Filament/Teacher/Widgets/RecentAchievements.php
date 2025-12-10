<?php

namespace App\Filament\Teacher\Widgets;

use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;
use App\Models\Achievement;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecentAchievements extends TableWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $teacher = Teacher::where('user_id', Auth::id())->first();

        if (!$teacher) {
            return $table
                ->query(Achievement::query()->whereRaw('1 = 0'))
                ->heading('Prestasi Siswa Terbaru');
        }

        $classIds = DB::table('class_subjects')
            ->where('teacher_id', $teacher->id)
            ->distinct('class_model_id')
            ->pluck('class_model_id');

        return $table
            ->query(
                Achievement::query()
                    ->with(['student.user', 'student.classModel'])
                    ->whereHas('student', function ($query) use ($classIds) {
                        $query->whereIn('class_id', $classIds);
                    })
                    ->latest()
                    ->limit(10)
            )
            ->heading('Prestasi Siswa Terbaru')
            ->columns([
                TextColumn::make('student.user.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('student.classModel.name')
                    ->label('Kelas')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('name')
                    ->label('Prestasi')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('level')
                    ->label('Tingkat')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Internasional' => 'danger',
                        'Nasional' => 'warning',
                        'Provinsi' => 'success',
                        'Kabupaten' => 'info',
                        'Kecamatan' => 'primary',
                        default => 'gray',
                    }),

                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->paginated(false)
            ->emptyStateHeading('Tidak ada prestasi')
            ->emptyStateDescription('Belum ada prestasi siswa yang tercatat.')
            ->emptyStateIcon('heroicon-o-trophy');
    }
}
