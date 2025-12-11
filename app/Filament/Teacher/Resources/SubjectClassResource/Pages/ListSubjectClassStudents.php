<?php

namespace App\Filament\Teacher\Resources\SubjectClassResource\Pages;

use App\Filament\Teacher\Resources\SubjectClassResource;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Semester;
use App\Models\ReportCard;
use App\Models\ReportCardGrade;
use App\Models\TahfidzDetail;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Tables\Table;
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
        $subject = Subject::find($this->subjectId);
        $isTahfidz = $this->isTahfidzSubject($subject);

        if ($isTahfidz) {
            return [];
        }

        return [
            Action::make('input_nilai')
                ->label('Input Nilai')
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->modalHeading('Input Nilai Siswa')
                ->modalDescription('Kosongkan field untuk tidak mengubah nilai yang sudah ada')
                ->modalWidth('3xl')
                ->modalSubmitActionLabel('Simpan Nilai')
                ->form(fn () => $this->getRegularGradeForm())
                ->action(fn (array $data) => $this->saveRegularGrades($data))
                ->after(fn () => $this->refreshKey++),
        ];
    }

    public function table(Table $table): Table
    {
        $subject = Subject::find($this->subjectId);
        $isTahfidz = $this->isTahfidzSubject($subject);

        $table = parent::table($table);

        if ($isTahfidz) {
            $table->recordActions([
                Action::make('input_nilai_tahfidz')
                    ->label('Input Nilai')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->url(fn (Student $record): string => SubjectClassResource::getUrl('tahfidz-grades', [
                        'subject' => $this->subjectId,
                        'class' => $this->classId,
                        'student' => $record->id,
                    ])),
            ]);
        }

        return $table;
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
        $semester = Semester::whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->first();

        $query = parent::getTableQuery()
            ->where('class_id', $this->classId);

        if ($semester) {
            $query->with(['reportCards' => function ($q) use ($semester) {
                $q->where('semester_id', $semester->id)
                  ->with(['grades' => function ($gq) {
                      $gq->where('subject_id', $this->subjectId);
                  }]);
            }]);
        }

        return $query;
    }

    protected function isTahfidzSubject($subject): bool
    {
        if (!$subject) return false;

        return stripos($subject->name, 'tahfidz') !== false ||
               stripos($subject->name, 'tahfiz') !== false;
    }

    protected function getRegularGradeForm(): array
    {
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
                ->placeholder($currentScore !== null ? "Nilai saat ini: {$currentScore}" : 'Masukkan nilai (0-100)')
                ->helperText($currentScore !== null ? "Kosongkan untuk tidak mengubah" : null);
        }

        return $fields;
    }

    protected function getTahfidzGradeForm(): array
    {
        $semester = Semester::whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->first();

        if (!$semester) {
            return [];
        }

        $students = Student::where('class_id', $this->classId)
            ->with('user')
            ->orderBy('nisn')
            ->get();

        $classTahfidz = ClassModel::find($this->classId)
            ->tahfidz()
            ->orderBy('juz')
            ->orderBy('name')
            ->get();

        if ($classTahfidz->isEmpty()) {
            return [
                Section::make('no_tahfidz')
                    ->content('Belum ada tahfidz yang ditambahkan ke kelas ini. Silakan tambahkan tahfidz di menu Kelas terlebih dahulu.')
            ];
        }

        $fields = [];

        foreach ($students as $index => $student) {
            $reportCard = ReportCard::where('student_id', $student->id)
                ->where('semester_id', $semester->id)
                ->first();

            $existingGrades = [];
            if ($reportCard) {
                $tahfidzDetails = TahfidzDetail::where('report_card_id', $reportCard->id)
                    ->get()
                    ->keyBy('tahfidz_id');

                foreach ($tahfidzDetails as $detail) {
                    $existingGrades[$detail->tahfidz_id] = $detail->grade;
                }
            }

            $fields[] = Section::make("student_header_{$student->id}")
                ->content(fn () => new \Illuminate\Support\HtmlString(
                    '<div class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">' .
                    ($index + 1) . ". {$student->nisn} - {$student->user->name}" .
                    '</div>'
                ));

            foreach ($classTahfidz as $tahfidz) {
                $fields[] = TextInput::make("tahfidz_{$student->id}_{$tahfidz->id}")
                    ->label($tahfidz->name . ($tahfidz->arabic_name ? " ({$tahfidz->arabic_name})" : ''))
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default($existingGrades[$tahfidz->id] ?? null)
                    ->placeholder('0-100')
                    ->columnSpan(1);
            }
        }

        return $fields;
    }

    protected function saveRegularGrades(array $data): void
    {
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
        $hasData = false;

        foreach ($data as $key => $value) {
            if (strpos($key, 'score_') === 0) {
                if ($value === null || $value === '') {
                    continue;
                }

                $hasData = true;
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

        if (!$hasData) {
            Notification::make()
                ->warning()
                ->title('Tidak ada perubahan')
                ->body('Tidak ada nilai yang diinput.')
                ->send();
            return;
        }

        Notification::make()
            ->success()
            ->title('Nilai berhasil disimpan')
            ->body("{$savedCount} nilai berhasil disimpan.")
            ->send();
    }

    protected function saveTahfidzGrades(array $data): void
    {
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
        $studentGrades = [];

        foreach ($data as $key => $value) {
            if (strpos($key, 'tahfidz_') === 0 && $value !== null && $value !== '') {
                $parts = explode('_', $key);
                if (count($parts) === 3) {
                    $studentId = $parts[1];
                    $tahfidzId = $parts[2];

                    if (!isset($studentGrades[$studentId])) {
                        $studentGrades[$studentId] = [];
                    }
                    $studentGrades[$studentId][$tahfidzId] = $value;
                }
            }
        }

        foreach ($studentGrades as $studentId => $tahfidzGrades) {
            $reportCard = ReportCard::firstOrCreate([
                'student_id' => $studentId,
                'semester_id' => $semester->id,
            ]);

            foreach ($tahfidzGrades as $tahfidzId => $grade) {
                TahfidzDetail::updateOrCreate(
                    [
                        'report_card_id' => $reportCard->id,
                        'tahfidz_id' => $tahfidzId,
                    ],
                    [
                        'grade' => $grade,
                    ]
                );
                $savedCount++;
            }

            if (!empty($tahfidzGrades)) {
                $average = round(array_sum($tahfidzGrades) / count($tahfidzGrades), 1);

                ReportCardGrade::updateOrCreate(
                    [
                        'report_card_id' => $reportCard->id,
                        'subject_id' => $this->subjectId,
                    ],
                    [
                        'grade' => $average,
                    ]
                );
            }
        }

        Notification::make()
            ->success()
            ->title('Nilai Tahfidz berhasil disimpan')
            ->body("{$savedCount} nilai detail dan rata-rata berhasil disimpan.")
            ->send();
    }
}
