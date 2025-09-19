<?php

namespace App\Filament\Teacher\Pages;

use App\Models\Attendance;
use App\Models\ClassModel;
use App\Models\ReportCard;
use App\Models\ReportCardGrade;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectDetail;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class GradingForm extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.teacher.pages.grading-form';

    protected static ?string $navigationLabel = null; // Hidden from navigation

    protected static bool $shouldRegisterNavigation = false; // Don't register in navigation

    protected static ?string $title = 'Form Penilaian';

    public ?array $data = [];
    public Student $student;
    public Semester $semester;
    public ClassModel $class;
    public $subjects = [];
    public $attendanceSummary = [];
    public $grades = [];

    public function mount($student, $semester, $class): void
    {
        $this->student = Student::with('user')->findOrFail($student);
        $this->semester = Semester::with('academicYear')->findOrFail($semester);
        $this->class = ClassModel::with('subjects.details')->findOrFail($class);
        
        $this->loadSubjects();
        $this->loadAttendanceSummary();
        $this->loadExistingGrades();
        
        $this->form->fill($this->getFormData());
    }

    private function loadSubjects(): void
    {
        $this->subjects = $this->class->subjects()->with('details')->get();
    }

    private function loadAttendanceSummary(): void
    {
        $attendances = Attendance::where('student_id', $this->student->id)
            ->where('semester_id', $this->semester->id)
            ->get();

        $this->attendanceSummary = [
            'Hadir' => $attendances->where('status', 'Hadir')->count(),
            'Sakit' => $attendances->where('status', 'Sakit')->count(),
            'Izin' => $attendances->where('status', 'Izin')->count(),
            'Alpha' => $attendances->where('status', 'Alpha')->count(),
        ];
    }

    private function loadExistingGrades(): void
    {
        $reportCard = ReportCard::where([
            'student_id' => $this->student->id,
            'semester_id' => $this->semester->id,
        ])->first();

        if ($reportCard) {
            $this->grades = ReportCardGrade::where('report_card_id', $reportCard->id)
                ->pluck('grade', 'subject_id')
                ->toArray();
        }
    }

    private function getFormData(): array
    {
        $formData = [
            'teacher_comment' => '',
        ];

        // Load existing report card data if exists
        $reportCard = ReportCard::where([
            'student_id' => $this->student->id,
            'semester_id' => $this->semester->id,
        ])->first();

        if ($reportCard) {
            $formData['teacher_comment'] = $reportCard->teacher_comment;
        }

        // Add grades for each subject
        foreach ($this->subjects as $subject) {
            $gradeKey = "grade_" . $subject->id;
            $formData[$gradeKey] = $this->grades[$subject->id] ?? '';
        }

        return $formData;
    }

    public function form($form)
    {
        $schema = [];

        // Add grade inputs for each subject
        foreach ($this->subjects as $subject) {
            if ($subject->details->isNotEmpty()) {
                // Subject with details
                $schema[] = TextInput::make("grade_" . $subject->id)
                    ->label($subject->name . ' (Rata-rata)')
                    ->placeholder('Contoh: 85')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->helperText('Detail: ' . $subject->details->pluck('name')->join(', '));
            } else {
                // Subject without details
                $schema[] = TextInput::make("grade_" . $subject->id)
                    ->label($subject->name)
                    ->placeholder('Contoh: 85')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100);
            }
        }

        // Add teacher comment
        $schema[] = Textarea::make('teacher_comment')
            ->label('Komentar Wali Kelas')
            ->rows(4)
            ->placeholder('Masukkan komentar untuk siswa...');

        return $form
            ->schema($schema)
            ->statePath('data')
            ->columns(2);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        try {
            DB::transaction(function () use ($data) {
                // Create or update report card
                $reportCard = ReportCard::updateOrCreate(
                    [
                        'student_id' => $this->student->id,
                        'semester_id' => $this->semester->id,
                    ],
                    [
                        'teacher_comment' => $data['teacher_comment'],
                        'attendance' => json_encode($this->attendanceSummary),
                    ]
                );

                // Save grades
                foreach ($this->subjects as $subject) {
                    $gradeKey = "grade_" . $subject->id;
                    if (isset($data[$gradeKey]) && $data[$gradeKey] !== '') {
                        ReportCardGrade::updateOrCreate(
                            [
                                'report_card_id' => $reportCard->id,
                                'subject_id' => $subject->id,
                            ],
                            [
                                'grade' => $data[$gradeKey],
                                'description' => $this->getGradeDescription($data[$gradeKey]),
                            ]
                        );
                    }
                }
            });

            Notification::make()
                ->title('Berhasil!')
                ->body('Nilai siswa telah disimpan.')
                ->success()
                ->send();

            // Redirect will be handled by browser
            $this->redirect(route('filament.teacher.pages.grade-management'));

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error!')
                ->body('Terjadi kesalahan saat menyimpan data.')
                ->danger()
                ->send();
        }
    }

    private function getGradeDescription($grade): string
    {
        if ($grade >= 90) return 'Sangat Baik';
        if ($grade >= 80) return 'Baik';
        if ($grade >= 70) return 'Cukup';
        if ($grade >= 60) return 'Kurang';
        return 'Sangat Kurang';
    }

    public function getTitle(): string
    {
        return "Penilaian - " . $this->student->user->name;
    }
}
