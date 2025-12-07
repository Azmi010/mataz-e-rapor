<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\SubjectClassResource\Pages;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\Semester;
use App\Models\ReportCard;
use App\Models\ReportCardGrade;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class SubjectClassResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function table(Table $table): Table
    {
        $subjectId = request()->route('subject');
        $classId = request()->route('class');

        return $table
            ->heading(function () use ($subjectId, $classId) {
                $subject = Subject::find($subjectId);
                $class = ClassModel::find($classId);
                return "Daftar Siswa - {$subject?->name} - Kelas {$class?->name}";
            })
            ->columns([
                TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                TextInputColumn::make('knowledge_score')
                    ->label('Nilai Pengetahuan')
                    ->rules(['nullable', 'numeric', 'min:0', 'max:100'])
                    ->getStateUsing(function (Student $record) use ($subjectId) {
                        $semester = Semester::whereDate('start_date', '<=', now())
                            ->whereDate('end_date', '>=', now())
                            ->first();

                        if (!$semester) return null;

                        $reportCard = ReportCard::where('student_id', $record->id)
                            ->where('semester_id', $semester->id)
                            ->first();

                        if (!$reportCard) return null;

                        $grade = ReportCardGrade::where('report_card_id', $reportCard->id)
                            ->where('subject_id', $subjectId)
                            ->first();

                        return $grade?->knowledge_score;
                    })
                    ->updateStateUsing(function (Student $record, $state) use ($subjectId) {
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

                        $reportCard = ReportCard::firstOrCreate([
                            'student_id' => $record->id,
                            'semester_id' => $semester->id,
                        ]);

                        ReportCardGrade::updateOrCreate(
                            [
                                'report_card_id' => $reportCard->id,
                                'subject_id' => $subjectId,
                            ],
                            [
                                'knowledge_score' => $state,
                            ]
                        );

                        Notification::make()
                            ->success()
                            ->title('Nilai pengetahuan berhasil disimpan')
                            ->send();
                    }),
                TextInputColumn::make('skill_score')
                    ->label('Nilai Keterampilan')
                    ->rules(['nullable', 'numeric', 'min:0', 'max:100'])
                    ->getStateUsing(function (Student $record) use ($subjectId) {
                        $semester = Semester::whereDate('start_date', '<=', now())
                            ->whereDate('end_date', '>=', now())
                            ->first();

                        if (!$semester) return null;

                        $reportCard = ReportCard::where('student_id', $record->id)
                            ->where('semester_id', $semester->id)
                            ->first();

                        if (!$reportCard) return null;

                        $grade = ReportCardGrade::where('report_card_id', $reportCard->id)
                            ->where('subject_id', $subjectId)
                            ->first();

                        return $grade?->skill_score;
                    })
                    ->updateStateUsing(function (Student $record, $state) use ($subjectId) {
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

                        $reportCard = ReportCard::firstOrCreate([
                            'student_id' => $record->id,
                            'semester_id' => $semester->id,
                        ]);

                        ReportCardGrade::updateOrCreate(
                            [
                                'report_card_id' => $reportCard->id,
                                'subject_id' => $subjectId,
                            ],
                            [
                                'skill_score' => $state,
                            ]
                        );

                        Notification::make()
                            ->success()
                            ->title('Nilai keterampilan berhasil disimpan')
                            ->send();
                    }),
                TextColumn::make('final_score')
                    ->label('Nilai Akhir')
                    ->getStateUsing(function (Student $record) use ($subjectId) {
                        $semester = Semester::whereDate('start_date', '<=', now())
                            ->whereDate('end_date', '>=', now())
                            ->first();

                        if (!$semester) return '-';

                        $reportCard = ReportCard::where('student_id', $record->id)
                            ->where('semester_id', $semester->id)
                            ->first();

                        if (!$reportCard) return '-';

                        $grade = ReportCardGrade::where('report_card_id', $reportCard->id)
                            ->where('subject_id', $subjectId)
                            ->first();

                        if (!$grade || !$grade->knowledge_score || !$grade->skill_score) {
                            return '-';
                        }

                        $avg = ($grade->knowledge_score + $grade->skill_score) / 2;
                        return number_format($avg, 0);
                    })
                    ->badge()
                    ->color(fn ($state) => match(true) {
                        $state === '-' => 'gray',
                        (int)$state >= 80 => 'success',
                        (int)$state >= 70 => 'warning',
                        default => 'danger'
                    }),
                TextColumn::make('status_nilai')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function (Student $record) use ($subjectId) {
                        $semester = Semester::whereDate('start_date', '<=', now())
                            ->whereDate('end_date', '>=', now())
                            ->first();

                        if (!$semester) {
                            return 'Tidak ada semester aktif';
                        }

                        $reportCard = ReportCard::where('student_id', $record->id)
                            ->where('semester_id', $semester->id)
                            ->first();

                        if (!$reportCard) {
                            return 'Belum Dinilai';
                        }

                        $grade = ReportCardGrade::where('report_card_id', $reportCard->id)
                            ->where('subject_id', $subjectId)
                            ->first();

                        if (!$grade || !$grade->knowledge_score || !$grade->skill_score) {
                            return 'Belum Lengkap';
                        }

                        return 'Sudah Dinilai';
                    })
                    ->color(fn ($state) => match($state) {
                        'Sudah Dinilai' => 'success',
                        'Belum Lengkap' => 'warning',
                        'Belum Dinilai' => 'warning',
                        default => 'gray'
                    }),
            ])
            ->defaultSort('nis', 'asc')
            ->poll('30s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubjectClassStudents::route('/{subject}/{class}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user']);
    }
}
