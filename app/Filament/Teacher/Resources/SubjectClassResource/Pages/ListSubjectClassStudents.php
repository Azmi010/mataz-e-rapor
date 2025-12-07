<?php

namespace App\Filament\Teacher\Resources\SubjectClassResource\Pages;

use App\Filament\Teacher\Resources\SubjectClassResource;
use App\Models\Teacher;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Semester;
use App\Models\ReportCard;
use App\Models\ReportCardGrade;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Hidden;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class ListSubjectClassStudents extends ListRecords
{
    protected static string $resource = SubjectClassResource::class;

    protected static bool $shouldRegisterNavigation = false;

    public $subjectId;
    public $classId;
    public $refreshKey = 0;

    public function mount(): void
    {
        $this->subjectId = request()->route('subject');
        $this->classId = request()->route('class');

        parent::mount();
    }

    public function getTitle(): string
    {
        $subject = Subject::find($this->subjectId);
        $class = ClassModel::find($this->classId);

        return "Daftar Siswa - {$subject?->name} ({$class?->name})";
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('input_nilai')
                ->label('Input Nilai')
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->modalHeading('Input Nilai Siswa')
                ->modalWidth('3xl')
                ->form(function () {
                    $semester = Semester::whereDate('start_date', '<=', now())
                        ->whereDate('end_date', '>=', now())
                        ->first();

                    if (!$semester) {
                        return [];
                    }

                    $students = Student::where('class_id', $this->classId)
                        ->orderBy('nisn')
                        ->get();

                    $fields = [];
                    foreach ($students as $index => $student) {
                        $reportCard = ReportCard::where('student_id', $student->id)
                            ->where('semester_id', $semester->id)
                            ->first();

                        $currentScore = null;
                        if ($reportCard) {
                            $grade = ReportCardGrade::where('report_card_id', $reportCard->id)
                                ->where('subject_id', $this->subjectId)
                                ->first();
                            $currentScore = $grade?->grade;
                        }

                        $fields[] = TextInput::make("score_{$student->id}")
                            ->label(($index + 1) . ". {$student->nisn} - {$student->user->name}")
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default($currentScore)
                            ->placeholder('Masukkan nilai (0-100)');
                    }

                    return $fields;
                })
                ->action(function (array $data) {
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

                    $savedCount = 0;
                    foreach ($data as $key => $value) {
                        if (strpos($key, 'score_') === 0 && $value !== null && $value !== '') {
                            $studentId = str_replace('score_', '', $key);

                            $reportCard = ReportCard::firstOrCreate([
                                'student_id' => $studentId,
                                'semester_id' => $semester->id,
                            ]);

                            ReportCardGrade::updateOrCreate(
                                [
                                    'report_card_id' => $reportCard->id,
                                    'subject_id' => $this->subjectId,
                                ],
                                [
                                    'grade' => $value,
                                ]
                            );

                            $savedCount++;
                        }
                    }

                    Notification::make()
                        ->success()
                        ->title('Nilai berhasil disimpan')
                        ->body("{$savedCount} nilai berhasil disimpan.")
                        ->send();
                })
                ->successRedirectUrl(fn () => static::getUrl([
                    'subject' => $this->subjectId,
                    'class' => $this->classId,
                ])),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public static function getUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?\Illuminate\Database\Eloquent\Model $tenant = null, bool $shouldGuessMissingParameters = false): string
    {
        if (empty($parameters) && request()->route('subject') && request()->route('class')) {
            $parameters = [
                'subject' => request()->route('subject'),
                'class' => request()->route('class'),
            ];
        }

        return parent::getUrl($parameters, $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters);
    }

    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()
            ->where('class_id', $this->classId);
    }
}
