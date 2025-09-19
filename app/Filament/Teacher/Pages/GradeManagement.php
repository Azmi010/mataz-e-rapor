<?php

namespace App\Filament\Teacher\Pages;

use App\Models\Attendance;
use App\Models\ClassModel;
use App\Models\ReportCard;
use App\Models\ReportCardGrade;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class GradeManagement extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.teacher.pages.grade-management';

    protected static ?string $navigationLabel = 'Manajemen Nilai';

    protected static ?string $title = 'Manajemen Nilai';

    protected static ?int $navigationSort = 2;

    public ?array $data = [];

    public $semester_id = null;
    public $class_id = null;
    public $students = [];
    
    // Modal state
    public $showGradingModal = false;
    public $selectedStudent = null;
    public $selectedStudentSubjects = [];
    public $attendanceSummary = [];
    public $grades = [];
    public $gradingData = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('semester_id')
                ->label('Pilih Semester')
                ->options(function () {
                    return Semester::whereHas('academicYear', fn($q) => $q->where('status', true))
                        ->get()
                        ->mapWithKeys(function ($semester) {
                            return [$semester->id => $semester->academicYear->year . ' - ' . $semester->name];
                        });
                })
                ->live()
                ->afterStateUpdated(function ($state) {
                    $this->semester_id = $state;
                    $this->loadClassData($this->class_id);
                }),

            Select::make('class_id')
                ->label('Pilih Kelas')
                ->options(ClassModel::query()
                    ->whereHas('academicYear', fn($q) => $q->where('status', true))
                    ->pluck('name', 'id'))
                ->live()
                ->afterStateUpdated(function ($state) {
                    $this->class_id = $state;
                    $this->loadClassData($state);
                }),
        ];
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    protected function getFormColumns(): int
    {
        return 2;
    }

    public function loadSemesterData($semesterId): void
    {
        $this->semester_id = $semesterId;
        $this->class_id = null;
        $this->students = [];
        $this->data['class_id'] = null;
    }

    public function loadClassData($classId): void
    {
        $this->class_id = $classId;
        if ($classId && $this->semester_id) {
            $this->students = Student::where('class_id', $classId)
                ->with('user')
                ->get();
        } else {
            $this->students = [];
        }
    }

    public function openGradingForm($studentId)
    {
        if ($this->semester_id && $this->class_id) {
            $this->selectedStudent = Student::with('user')->findOrFail($studentId);
            $this->loadStudentData();
            $this->showGradingModal = true;
        }
    }

    public function closeGradingModal()
    {
        $this->showGradingModal = false;
        $this->selectedStudent = null;
        $this->gradingData = [];
    }

    private function loadStudentData()
    {
        if (!$this->selectedStudent || !$this->semester_id || !$this->class_id) {
            return;
        }

        // Load subjects for the class
        $class = ClassModel::with('subjects.details')->findOrFail($this->class_id);
        $this->selectedStudentSubjects = $class->subjects;

        // Load attendance summary
        $attendances = Attendance::where('student_id', $this->selectedStudent->id)
            ->where('semester_id', $this->semester_id)
            ->get();

        $this->attendanceSummary = [
            'Hadir' => $attendances->where('status', 'Hadir')->count(),
            'Sakit' => $attendances->where('status', 'Sakit')->count(),
            'Izin' => $attendances->where('status', 'Izin')->count(),
            'Alpha' => $attendances->where('status', 'Alpha')->count(),
        ];

        // Load existing grades
        $reportCard = ReportCard::where([
            'student_id' => $this->selectedStudent->id,
            'semester_id' => $this->semester_id,
        ])->first();

        $existingGrades = [];
        if ($reportCard) {
            $existingGrades = ReportCardGrade::where('report_card_id', $reportCard->id)
                ->pluck('grade', 'subject_id')
                ->toArray();
        }

        // Setup form data
        $this->gradingData = [
            'teacher_comment' => $reportCard->teacher_comment ?? '',
        ];

        foreach ($this->selectedStudentSubjects as $subject) {
            $this->gradingData["grade_" . $subject->id] = $existingGrades[$subject->id] ?? '';
        }
    }

    public function saveGrades()
    {
        try {
            DB::transaction(function () {
                // Create or update report card
                $reportCard = ReportCard::updateOrCreate(
                    [
                        'student_id' => $this->selectedStudent->id,
                        'semester_id' => $this->semester_id,
                    ],
                    [
                        'teacher_comment' => $this->gradingData['teacher_comment'],
                        'attendance' => json_encode($this->attendanceSummary),
                    ]
                );

                // Save grades
                foreach ($this->selectedStudentSubjects as $subject) {
                    $gradeKey = "grade_" . $subject->id;
                    if (isset($this->gradingData[$gradeKey]) && $this->gradingData[$gradeKey] !== '') {
                        ReportCardGrade::updateOrCreate(
                            [
                                'report_card_id' => $reportCard->id,
                                'subject_id' => $subject->id,
                            ],
                            [
                                'grade' => $this->gradingData[$gradeKey],
                                'description' => $this->getGradeDescription($this->gradingData[$gradeKey]),
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

            $this->closeGradingModal();

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

    protected function getViewData(): array
    {
        return [
            'students' => $this->students,
            'semesters' => $this->semesters,
            'classes' => $this->classes,
            'selectedStudent' => $this->selectedStudent,
            'selectedStudentSubjects' => $this->selectedStudentSubjects,
            'attendanceSummary' => $this->attendanceSummary,
            'gradingData' => $this->gradingData,
            'showGradingModal' => $this->showGradingModal,
        ];
    }
}
