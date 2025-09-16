<?php

namespace App\Filament\Teacher\Resources\DailyActivities\Tables;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\ClassModel;
use App\Models\DailyActivity;
use App\Models\Student;
use App\Models\Semester;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class DailyActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Student::query()->with(['user', 'classModel']))
            ->columns(self::getColumns($table))
            ->filters(self::getFilters())
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(2)
            ->recordActions([
                Action::make('setAttendance')
                    ->label('Set Kehadiran')
                    ->icon('heroicon-o-check-circle')
                    ->modalHeading('Set Kehadiran Siswa')
                    ->form([
                        Select::make('status')
                            ->label('Status Kehadiran')
                            ->options([
                                'Hadir' => 'Hadir',
                                'Sakit' => 'Sakit',
                                'Izin' => 'Izin',
                                'Alpha' => 'Alpha',
                            ])
                            ->placeholder('Belum diset / kosongkan')
                            ->native(false)
                            ->searchable()
                            ->hint('Kosongkan lalu Simpan untuk menghapus'),
                    ])
                    ->fillForm(function (Student $record) {
                        $date = self::getCurrentDate();
                        $status = Attendance::where([
                            'student_id' => $record->id,
                            'date' => $date,
                        ])->value('status');
                        return ['status' => $status];
                    })
                    ->action(function (Student $record, array $data) {
                        $date = self::getCurrentDate();
                        $status = $data['status'] ?? null;
                        if (!$status) {
                            Attendance::where([
                                'student_id' => $record->id,
                                'date' => $date,
                            ])->delete();
                            Notification::make()
                                ->title('Kehadiran dihapus')
                                ->success()
                                ->send();
                            return;
                        }
                        self::updateAttendance($record->id, $status, $date);
                        Notification::make()
                            ->title('Kehadiran disimpan')
                            ->success()
                            ->send();
                    })
                    ->closeModalByClickingAway(false),
            ])
            ->toolbarActions([
                //
            ])
            ->emptyStateHeading('Pilih kelas untuk melihat daftar siswa')
            ->emptyStateDescription('Gunakan filter di atas untuk memilih kelas dan tanggal.')
            ->emptyStateIcon('heroicon-o-academic-cap');
    }

    protected static function getColumns($table): array
    {
        $columns = [
            TextColumn::make('user.name')
                ->label('Nama Siswa')
                ->searchable()
                ->sortable(),

            TextColumn::make('nis')
                ->label('NIS')
                ->searchable()
                ->sortable(),
        ];

        $activities = Activity::schoolOnly()->get();
        foreach ($activities as $activity) {
            $columns[] = CheckboxColumn::make("activity_{$activity->id}")
                ->label($activity->name)
                ->getStateUsing(function (Student $record, $component = null) use ($activity) {
                    $date = self::getCurrentDate($component);

                    return DailyActivity::where([
                        'student_id' => $record->id,
                        'activity_id' => $activity->id,
                        'activity_date' => $date,
                    ])->exists();
                })
                ->updateStateUsing(function (Student $record, $state, $component = null) use ($activity) {
                    $date = self::getCurrentDate($component);
                    self::toggleActivity($record->id, $activity->id, $state, $date);
                });
        }

        $columns[] = TextColumn::make('attendance_status')
            ->label('Kehadiran')
            ->getStateUsing(function (Student $record, $component = null) {
                $date = self::getCurrentDate($component);
                return Attendance::where([
                    'student_id' => $record->id,
                    'date' => $date,
                ])->value('status') ?? 'Belum diset';
            })
            ->badge()
            ->color(fn($state) => match ($state) {
                'Hadir' => 'success',
                'Sakit' => 'warning',
                'Izin' => 'info',
                'Alpha' => 'danger',
                default => 'gray',
            });

        return $columns;
    }

    protected static function getCurrentDate($component = null): string
    {
        $sessionDate = session('daily_activity_date');
        if ($sessionDate) {
            return $sessionDate;
        }

        $sources = [
            request()->get('tableFilters.activity_date.activity_date'),
            request()->input('tableFilters.activity_date.activity_date'),
            request()->query('tableFilters.activity_date.activity_date'),
        ];

        foreach ($sources as $source) {
            if ($source) {
                session(['daily_activity_date' => $source]);
                return $source;
            }
        }

        $today = now()->format('Y-m-d');
        session(['daily_activity_date' => $today]);
        return $today;
    }

    protected static function getFilters(): array
    {
        return [
            Filter::make('class_filter')
                ->label('Filter Kelas')
                ->form([
                    Select::make('class_id')
                        ->label('Pilih Kelas')
                        ->placeholder('Pilih kelas...')
                        ->options(ClassModel::query()
                            ->whereHas('academicYear', fn($q) => $q->where('status', true))
                            ->pluck('name', 'id'))
                        ->live()
                        ->required(),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query->when($data['class_id'], fn($q) => $q->where('class_id', $data['class_id']));
                }),

            Filter::make('activity_date')
                ->label('Filter Tanggal')
                ->form([
                    DatePicker::make('activity_date')
                        ->label('Tanggal')
                        ->default(session('daily_activity_date', now()))
                        ->live()
                        ->afterStateUpdated(function ($state) {
                            session(['daily_activity_date' => $state]);
                        })
                        ->required(),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query;
                }),
        ];
    }

    protected static function toggleActivity($studentId, $activityId, $state, $date = null): void
    {
        if (!$date) {
            $date = now()->format('Y-m-d');
        }

        try {
            $semester = Semester::whereHas('academicYear', fn($q) => $q->where('status', true))
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->first();

            if (!$semester) {
                Notification::make()
                    ->title('Error')
                    ->body('Tidak ada semester aktif untuk tanggal ini')
                    ->warning()
                    ->send();
                return;
            }

            if ($state) {
                DailyActivity::updateOrCreate([
                    'student_id' => $studentId,
                    'activity_id' => $activityId,
                    'activity_date' => $date,
                ], [
                    'semester_id' => $semester->id,
                ]);
            } else {
                DailyActivity::where([
                    'student_id' => $studentId,
                    'activity_id' => $activityId,
                    'activity_date' => $date,
                ])->delete();
            }

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Gagal menyimpan aktivitas: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected static function updateAttendance($studentId, $status, $date = null): void
    {
        if (!$date) {
            $date = now()->format('Y-m-d');
        }

        try {
            if ($status === null || $status === 'Belum diset') {
                Attendance::where([
                    'student_id' => $studentId,
                    'date' => $date,
                ])->delete();
                return;
            }

            $semester = Semester::whereHas('academicYear', fn($q) => $q->where('status', true))
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->first();

            if (!$semester) {
                $semester = Semester::first();
                if (!$semester) {
                    return;
                }
            }

            Attendance::updateOrCreate([
                'student_id' => $studentId,
                'date' => $date,
            ], [
                'semester_id' => $semester->id,
                'status' => $status,
            ]);

        } catch (\Exception $e) {
            Log::error('Update attendance failed', [
                'student_id' => $studentId,
                'status' => $status,
                'date' => $date,
                'error' => $e->getMessage()
            ]);
        }
    }
}
