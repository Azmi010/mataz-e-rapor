<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\SubjectClassResource\Pages;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Semester;
use App\Models\ReportCard;
use App\Models\ReportCardGrade;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class SubjectClassResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function table(Table $table): Table
    {
        return $table
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
                    ->state(function (Student $record) {
                        $subjectId = request()->route('subject');

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
                    })
                    ->tooltip(function (Student $record) {
                        $subjectId = request()->route('subject');
                        $subject = \App\Models\Subject::find($subjectId);

                        if (!$subject || (stripos($subject->name, 'tahfidz') === false && stripos($subject->name, 'tahfiz') === false)) {
                            return null;
                        }

                        $semester = Semester::whereDate('start_date', '<=', now())
                            ->whereDate('end_date', '>=', now())
                            ->first();

                        if (!$semester) return null;

                        $reportCard = ReportCard::where('student_id', $record->id)
                            ->where('semester_id', $semester->id)
                            ->first();

                        if (!$reportCard) return null;

                        $tahfidzDetails = \App\Models\TahfidzDetail::where('report_card_id', $reportCard->id)
                            ->with('tahfidz')
                            ->get();

                        if ($tahfidzDetails->isEmpty()) return 'Belum ada nilai detail';

                        $tooltip = "Detail Nilai:\n";
                        foreach ($tahfidzDetails as $detail) {
                            $tooltip .= "• {$detail->tahfidz->name}: {$detail->grade}\n";
                        }

                        return $tooltip;
                    }),
            ])
            ->recordActions([
                Action::make('input_nilai_tahfidz')
                    ->label('Input Nilai')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->url(fn (Student $record): string => static::getUrl('tahfidz-grades', [
                        'subject' => request()->route('subject'),
                        'class' => request()->route('class'),
                        'student' => $record->id,
                    ]))
                    ->visible(function () {
                        $subjectId = request()->route('subject');
                        $subject = Subject::find($subjectId);
                        return $subject && (stripos($subject->name, 'tahfidz') !== false || stripos($subject->name, 'tahfiz') !== false);
                    }),
            ])
            ->defaultSort('nisn', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubjectClassStudents::route('/{subject}/{class}'),
            'tahfidz-grades' => Pages\TahfidzGrades::route('/{subject}/{class}/{student}/tahfidz'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user']);
    }
}
