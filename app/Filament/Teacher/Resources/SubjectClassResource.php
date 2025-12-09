<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\SubjectClassResource\Pages;
use App\Models\Student;
use App\Models\Semester;
use App\Models\ReportCard;
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
                    ->getStateUsing(function (Student $record) {
                        $reportCard = $record->reportCards->first();

                        if (!$reportCard) {
                            return '-';
                        }

                        $grade = $reportCard->grades->first();

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
