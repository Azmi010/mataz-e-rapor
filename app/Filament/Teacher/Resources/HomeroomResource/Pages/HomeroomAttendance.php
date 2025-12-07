<?php

namespace App\Filament\Teacher\Resources\HomeroomResource\Pages;

use App\Filament\Teacher\Resources\HomeroomResource;
use App\Models\Teacher;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Semester;
use Filament\Resources\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class HomeroomAttendance extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = HomeroomResource::class;

    protected string $view = 'filament.teacher.resources.homeroom.attendance';

    protected static bool $shouldRegisterNavigation = false;

    public $selectedDate;
    public $attendanceTemp = [];

    public function mount(): void
    {
        $this->selectedDate = now()->format('Y-m-d');
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    protected function getActions(): array
    {
        return [
            Action::make('date_picker')
                ->label('Pilih Tanggal')
                ->icon('heroicon-o-calendar')
                ->form([
                    DatePicker::make('date')
                        ->label('Tanggal Absensi')
                        ->default($this->selectedDate)
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->required(),
                ])
                ->modalSubmitActionLabel('Terapkan')
                ->action(function (array $data) {
                    $this->selectedDate = $data['date'];
                    $this->attendanceTemp = [];
                }),
        ];
    }

    public function updatedSelectedDate(): void
    {
        //
    }

    public function table(Table $table): Table
    {
        $teacher = Teacher::where('user_id', Auth::id())->first();
        $class = $teacher ? ClassModel::where('homeroom_teacher_id', $teacher->id)->first() : null;

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
                    ->formatStateUsing(fn (string $state): string => $state === 'male' ? 'L' : 'P'),
                SelectColumn::make('attendance')
                    ->label('Kehadiran')
                    ->options([
                        'Hadir' => 'Hadir',
                        'Sakit' => 'Sakit',
                        'Izin' => 'Izin',
                        'Alpha' => 'Alpha',
                    ])
                    ->getStateUsing(function (Student $record) {
                        if (isset($this->attendanceTemp[$record->id])) {
                            return $this->attendanceTemp[$record->id];
                        }

                        $attendance = Attendance::where('student_id', $record->id)
                            ->where('date', $this->selectedDate)
                            ->first();
                        return $attendance?->status ?? 'Hadir';
                    })
                    ->updateStateUsing(function (Student $record, $state) {
                        $this->attendanceTemp[$record->id] = $state;
                    }),
            ])
            ->headerActions([
                Action::make('save_all')
                    ->label('Simpan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function () {
                        $teacher = Teacher::where('user_id', Auth::id())->first();

                        if (!$teacher) {
                            Notification::make()
                                ->danger()
                                ->title('Data guru tidak ditemukan')
                                ->send();
                            return;
                        }

                        $class = ClassModel::where('homeroom_teacher_id', $teacher->id)->first();

                        if (!$class) {
                            Notification::make()
                                ->danger()
                                ->title('Anda bukan wali kelas')
                                ->send();
                            return;
                        }

                        $semester = Semester::whereDate('start_date', '<=', now())
                            ->whereDate('end_date', '>=', now())
                            ->first();

                        if (!$semester) {
                            Notification::make()
                                ->danger()
                                ->title('Tidak ada semester aktif')
                                ->send();
                            return;
                        }

                        $students = Student::where('class_id', $class->id)->get();
                        $savedCount = 0;

                        foreach ($students as $student) {
                            if (isset($this->attendanceTemp[$student->id])) {
                                $status = $this->attendanceTemp[$student->id];
                            } else {
                                $attendance = Attendance::where('student_id', $student->id)
                                    ->where('date', $this->selectedDate)
                                    ->first();
                                $status = $attendance?->status ?? 'Hadir';
                            }

                            Attendance::updateOrCreate(
                                [
                                    'student_id' => $student->id,
                                    'date' => $this->selectedDate,
                                ],
                                [
                                    'semester_id' => $semester->id,
                                    'status' => $status,
                                ]
                            );
                            $savedCount++;
                        }

                        $this->attendanceTemp = [];

                        Notification::make()
                            ->success()
                            ->title('Absensi berhasil disimpan')
                            ->body("{$savedCount} data absensi berhasil disimpan untuk tanggal " . \Carbon\Carbon::parse($this->selectedDate)->format('d/m/Y'))
                            ->send();
                    }),
            ])
            ->paginated(false);
    }

    public function getTitle(): string
    {
        return 'Absen Siswa';
    }

    public function getHeading(): string
    {
        return 'Absen Siswa';
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
