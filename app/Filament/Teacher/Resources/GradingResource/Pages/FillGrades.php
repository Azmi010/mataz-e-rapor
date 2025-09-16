<?php

namespace App\Filament\Teacher\Resources\GradingResource\Pages;

use App\Filament\Teacher\Resources\GradingResource;
use Filament\Resources\Pages\Page;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectDetail;
use App\Models\ReportCard;
use App\Models\ReportCardGrade;
use Illuminate\Support\Facades\DB;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class FillGrades extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static string $resource = GradingResource::class;
    protected string $view = 'filament.teacher.pages.fill-grades';

    public Student $record;
    public ?array $data = [];

    public function mount(Student $record)
    {
        $this->record = $record;
        $this->loadExistingGrades();
        $this->form->fill($this->data);
    }

    protected function loadExistingGrades(): void
    {
        $student = $this->record;

        Log::info('Student class_id: ' . $student->class_id);

        $allSubjects = Subject::with('details')->get();
        Log::info('Total subjects available: ' . $allSubjects->count());

        $pivotData = DB::table('class_subjects')->where('class_model_id', $student->class_id)->get();
        Log::info('Pivot data for class ' . $student->class_id . ': ' . $pivotData->toJson());

        $list = [];

        if ($pivotData->isEmpty()) {
            foreach ($allSubjects->take(3) as $subject) {
                if ($subject->details->isEmpty()) {
                    $list[] = [
                        'subject_id' => $subject->id,
                        'subject_detail_id' => null,
                        'display_name' => $subject->name . ' (ALL-NO-PIVOT)',
                        'score' => null,
                    ];
                } else {
                    foreach ($subject->details as $detail) {
                        $list[] = [
                            'subject_id' => $subject->id,
                            'subject_detail_id' => $detail->id,
                            'display_name' => $detail->name . ' (DETAIL-NO-PIVOT)',
                            'score' => null,
                        ];
                    }
                }
            }
        } else {
            $subjectIds = $pivotData->pluck('subject_id');
            $subjects = Subject::whereIn('id', $subjectIds)->with('details')->get();

            foreach ($subjects as $subject) {
                if ($subject->details->isEmpty()) {
                    $list[] = [
                        'subject_id' => $subject->id,
                        'subject_detail_id' => null,
                        'display_name' => $subject->name,
                        'score' => null,
                    ];
                } else {
                    foreach ($subject->details as $detail) {
                        $list[] = [
                            'subject_id' => $subject->id,
                            'subject_detail_id' => $detail->id,
                            'display_name' => $detail->name,
                            'score' => null,
                        ];
                    }
                }
            }
        }

        $semester = \App\Models\Semester::whereHas('academicYear', fn($q) => $q->where('status', true))
            ->orderByDesc('start_date')
            ->first();

        $reportCard = null;
        $existingGrades = collect();
        $teacherComment = null;

        if ($semester) {
            $reportCard = ReportCard::where([
                'student_id' => $student->id,
                'semester_id' => $semester->id,
            ])->first();

            if ($reportCard) {
                $existingGrades = ReportCardGrade::where('report_card_id', $reportCard->id)
                    ->get()
                    ->groupBy(fn($g) => $g->subject_id . '-' . ($g->subject_detail_id ?? 'base'));
                $teacherComment = $reportCard->teacher_comment;
            }
        }

        foreach ($list as &$item) {
            $key = $item['subject_id'] . '-' . ($item['subject_detail_id'] ?? 'base');
            $gradeModel = $existingGrades->get($key)?->first();
            $item['score'] = $gradeModel?->grade;
        }

        $this->data = [
            'grades' => $list,
            'teacher_comment' => $teacherComment,
        ];

        Log::info('Final grades array: ' . json_encode($list));
    }

    public function form($form)
    {
        return $form
            ->schema([
                Forms\Components\Repeater::make('grades')
                    ->label('Nilai Mapel')
                    ->schema([
                        Forms\Components\Hidden::make('subject_id'),
                        Forms\Components\Hidden::make('subject_detail_id'),
                        Forms\Components\TextInput::make('display_name')
                            ->label('Mata Pelajaran')
                            ->disabled(),
                        Forms\Components\TextInput::make('score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->label('Skor')
                            ->placeholder('')
                            ->helperText('Kosongkan jika belum diisi'),
                    ])
                    ->columns(3)
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false),
                Forms\Components\Textarea::make('teacher_comment')
                    ->label('Catatan Wali Kelas')
                    ->rows(4),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $student = $this->record;
        $semester = \App\Models\Semester::whereHas('academicYear', fn($q) => $q->where('status', true))
            ->orderByDesc('start_date')
            ->first();
        if (!$semester) {
            Notification::make()->title('Semester aktif tidak ditemukan')->danger()->send();
            return;
        }

        DB::beginTransaction();
        try {
            $reportCard = ReportCard::firstOrCreate([
                'student_id' => $student->id,
                'semester_id' => $semester->id,
            ]);

            $state = $this->form->getState();
            $input = collect($state['grades'] ?? []);
            $reportCard->teacher_comment = $state['teacher_comment'] ?? null;
            $reportCard->save();
            foreach ($input as $row) {
                if (!array_key_exists('subject_id', $row)) {
                    continue;
                }
                $hasExisting = ReportCardGrade::where([
                    'report_card_id' => $reportCard->id,
                    'subject_id' => $row['subject_id'],
                    'subject_detail_id' => $row['subject_detail_id'] ?? null,
                ])->first();
                $raw = $row['score'] ?? null;
                if ($raw === '' || $raw === null) {
                    continue;
                }
                $score = (int) $raw;
                ReportCardGrade::updateOrCreate([
                    'report_card_id' => $reportCard->id,
                    'subject_id' => $row['subject_id'],
                    'subject_detail_id' => $row['subject_detail_id'] ?? null,
                ], [
                    'grade' => $score,
                ]);
            }

            DB::commit();
            Notification::make()->title('Nilai berhasil disimpan')->success()->send();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            Notification::make()->title('Gagal menyimpan nilai')->body($e->getMessage())->danger()->send();
        }
    }

    public function getHeading(): string
    {
        return 'Isi Nilai - ' . ($this->record->user->name ?? 'Siswa');
    }
}
