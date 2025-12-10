<?php

namespace App\Filament\Teacher\Resources\HomeroomResource\Pages;

use App\Filament\Teacher\Resources\HomeroomResource;
use App\Models\Achievement;
use App\Models\Student;
use App\Models\Semester;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class HomeroomAchievements extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = HomeroomResource::class;

    protected string $view = 'filament.teacher.resources.homeroom.achievements';

    protected static bool $shouldRegisterNavigation = false;

    public $selectedAchievementId;

    public function getTitle(): string
    {
        return 'Prestasi Siswa';
    }

    public function getHeading(): string
    {
        return 'Prestasi Siswa';
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function table(Table $table): Table
    {
        $teacher = \App\Models\Teacher::where('user_id', Auth::id())->first();
        $class = $teacher ? \App\Models\ClassModel::where('homeroom_teacher_id', $teacher->id)->first() : null;

        return $table
            ->query(
                Student::query()
                    ->where('class_id', $class?->id)
                    ->with(['user'])
                    ->orderBy('nisn')
            )
            ->columns([
                TextColumn::make('no')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('nisn')
                    ->label('NISN')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('gender')
                    ->label('L/P')
                    ->formatStateUsing(fn (string $state): string => $state === 'L' ? 'L' : 'P'),
                TextColumn::make('prestasi')
                    ->label('Prestasi')
                    ->getStateUsing(function (Student $record) {
                        $semester = Semester::whereDate('start_date', '<=', now())
                            ->whereDate('end_date', '>=', now())
                            ->first();

                        if (!$semester) return '-';

                        $achievements = Achievement::where('student_id', $record->id)
                            ->where('semester_id', $semester->id)
                            ->get();

                        if ($achievements->isEmpty()) return '-';

                        return $achievements->map(function ($achievement) {
                            return "• {$achievement->name}" .
                                ($achievement->description ? " ({$achievement->description})" : "");
                        })->join("\n");
                    })
                    ->wrap()
                    ->html()
                    ->formatStateUsing(fn ($state) => nl2br(e($state))),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->visible(function (Student $record) {
                        $semester = Semester::whereDate('start_date', '<=', now())
                            ->whereDate('end_date', '>=', now())
                            ->first();

                        if (!$semester) return false;

                        return Achievement::where('student_id', $record->id)
                            ->where('semester_id', $semester->id)
                            ->exists();
                    })
                    ->form(function (Student $record) {
                        $semester = Semester::whereDate('start_date', '<=', now())
                            ->whereDate('end_date', '>=', now())
                            ->first();

                        return [
                            Select::make('achievement_id')
                                ->label('Pilih Prestasi')
                                ->options(
                                    Achievement::where('student_id', $record->id)
                                        ->where('semester_id', $semester->id)
                                        ->pluck('name', 'id')
                                )
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, $livewire) {
                                    if ($state) {
                                        $achievement = Achievement::find($state);
                                        $set('name', $achievement->name);
                                        $set('level', $achievement->level);
                                        $set('date', $achievement->date);
                                        $set('description', $achievement->description);
                                        $livewire->selectedAchievementId = $state;
                                    }
                                }),
                            TextInput::make('name')
                                ->label('Nama Prestasi')
                                ->required()
                                ->maxLength(255),
                            Select::make('level')
                                ->label('Tingkat')
                                ->options([
                                    'Kecamatan' => 'Kecamatan',
                                    'Kabupaten' => 'Kabupaten',
                                    'Provinsi' => 'Provinsi',
                                    'Nasional' => 'Nasional',
                                    'Internasional' => 'Internasional',
                                ])
                                ->required(),
                            \Filament\Forms\Components\DatePicker::make('date')
                                ->label('Tanggal')
                                ->required()
                                ->maxDate(now()),
                            Textarea::make('description')
                                ->label('Deskripsi')
                                ->rows(3)
                                ->maxLength(500),
                        ];
                    })
                    ->action(function (Student $record, array $data, $livewire) {
                        $achievement = Achievement::find($data['achievement_id']);

                        if ($achievement) {
                            $achievement->update([
                                'name' => $data['name'],
                                'level' => $data['level'],
                                'date' => $data['date'],
                                'description' => $data['description'] ?? null,
                            ]);

                            $livewire->selectedAchievementId = null;
                        }
                    })
                    ->modalSubmitActionLabel('Simpan')
                    ->successNotificationTitle('Prestasi berhasil diupdate')
                    ->extraModalFooterActions([
                        \Filament\Actions\Action::make('delete')
                            ->label('Hapus')
                            ->color('danger')
                            ->icon('heroicon-o-trash')
                            ->requiresConfirmation()
                            ->modalHeading('Hapus Prestasi')
                            ->modalDescription('Apakah Anda yakin ingin menghapus prestasi ini?')
                            ->modalSubmitActionLabel('Hapus')
                            ->action(function ($livewire) {
                                if ($livewire->selectedAchievementId) {
                                    $achievement = Achievement::find($livewire->selectedAchievementId);

                                    if ($achievement) {
                                        $achievement->delete();

                                        Notification::make()
                                            ->success()
                                            ->title('Prestasi berhasil dihapus')
                                            ->send();

                                        $livewire->selectedAchievementId = null;
                                    }
                                }
                            }),
                    ]),
            ])
            ->paginated(false);
    }

    protected function getHeaderActions(): array
    {
        $teacher = \App\Models\Teacher::where('user_id', Auth::id())->first();
        $class = $teacher ? \App\Models\ClassModel::where('homeroom_teacher_id', $teacher->id)->first() : null;

        $semester = Semester::whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->first();

        return [
            Action::make('tambahPrestasi')
                ->label('Tambah')
                ->color('primary')
                ->icon('heroicon-o-plus')
                ->disabled(!$semester)
                ->form([
                    Select::make('student_id')
                        ->label('Siswa')
                        ->options(function () use ($class) {
                            return Student::where('class_id', $class?->id)
                                ->join('users', 'students.user_id', '=', 'users.id')
                                ->orderBy('users.name')
                                ->pluck('users.name', 'students.id');
                        })
                        ->required()
                        ->searchable(),
                    TextInput::make('name')
                        ->label('Nama Prestasi')
                        ->required()
                        ->maxLength(255),
                    Select::make('level')
                        ->label('Tingkat')
                        ->options([
                            'Kecamatan' => 'Kecamatan',
                            'Kabupaten' => 'Kabupaten',
                            'Provinsi' => 'Provinsi',
                            'Nasional' => 'Nasional',
                            'Internasional' => 'Internasional',
                        ])
                        ->required(),
                    \Filament\Forms\Components\DatePicker::make('date')
                        ->label('Tanggal')
                        ->required()
                        ->maxDate(now()),
                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(3)
                        ->maxLength(500),
                ])
                ->action(function (array $data) use ($semester) {
                    Achievement::create([
                        'student_id' => $data['student_id'],
                        'semester_id' => $semester->id,
                        'name' => $data['name'],
                        'level' => $data['level'],
                        'date' => $data['date'],
                        'description' => $data['description'] ?? null,
                    ]);
                })
                ->successNotificationTitle('Prestasi berhasil ditambahkan'),
        ];
    }
}
