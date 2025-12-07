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
                TextColumn::make('score')
                    ->label('Nilai')
                    ->state(function (Student $record) use ($subjectId) {
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

                        return $grade?->grade ?? '-';
                    }),
            ])
            ->defaultSort('nisn', 'asc');
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
