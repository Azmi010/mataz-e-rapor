<?php

namespace App\Filament\Teacher\Resources\HomeroomResource\Pages;

use App\Filament\Teacher\Resources\HomeroomResource;
use App\Models\Teacher;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\ReportCard;
use App\Models\Semester;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class HomeroomNotes extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = HomeroomResource::class;

    protected string $view = 'filament.teacher.resources.homeroom.notes';

    protected static bool $shouldRegisterNavigation = false;

    public $notesTemp = [];

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
                    ->formatStateUsing(fn (string $state): string => $state === 'L' ? 'L' : 'P'),
                TextInputColumn::make('notes')
                    ->label('Catatan Wali Kelas')
                    ->placeholder('Tulis catatan untuk siswa...')
                    ->getStateUsing(function (Student $record) {
                        if (isset($this->notesTemp[$record->id])) {
                            return $this->notesTemp[$record->id];
                        }

                        $semester = Semester::whereDate('start_date', '<=', now())
                            ->whereDate('end_date', '>=', now())
                            ->first();

                        if (!$semester) return '';

                        $reportCard = ReportCard::where('student_id', $record->id)
                            ->where('semester_id', $semester->id)
                            ->first();

                        return $reportCard?->teacher_comment ?? '';
                    })
                    ->updateStateUsing(function (Student $record, $state) {
                        $this->notesTemp[$record->id] = $state;
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
                            if (isset($this->notesTemp[$student->id])) {
                                $note = $this->notesTemp[$student->id];
                            } else {
                                $reportCard = ReportCard::where('student_id', $student->id)
                                    ->where('semester_id', $semester->id)
                                    ->first();
                                $note = $reportCard?->teacher_comment ?? '';
                            }

                            if (!empty($note)) {
                                ReportCard::updateOrCreate(
                                    [
                                        'student_id' => $student->id,
                                        'semester_id' => $semester->id,
                                    ],
                                    [
                                        'teacher_comment' => $note,
                                    ]
                                );
                                $savedCount++;
                            }
                        }

                        $this->notesTemp = [];

                        Notification::make()
                            ->success()
                            ->title('Catatan berhasil disimpan')
                            ->body("{$savedCount} catatan berhasil disimpan.")
                            ->send();
                    }),
            ])
            ->paginated(false);
    }

    public function getTitle(): string
    {
        return 'Catatan Wali Kelas';
    }

    public function getHeading(): string
    {
        return 'Catatan Wali Kelas';
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
