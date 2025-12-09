<?php

namespace App\Filament\Teacher\Resources\SubjectClassResource\Pages;

use App\Filament\Teacher\Resources\SubjectClassResource;
use App\Models\Student;
use App\Models\Subject;
use App\Models\ClassModel;
use App\Models\Semester;
use App\Models\ReportCard;
use App\Models\ReportCardGrade;
use App\Models\Tahfidz;
use App\Models\TahfidzDetail;
use Filament\Resources\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TahfidzGrades extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = SubjectClassResource::class;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.teacher.resources.subject-class-resource.pages.tahfidz-grades';

    public $subjectId;
    public $classId;
    public $studentId;
    public $student;
    public $subject;
    public $class;

    public function mount(): void
    {
        $this->subjectId = request()->route('subject');
        $this->classId = request()->route('class');
        $this->studentId = request()->route('student');

        $this->student = Student::with('user')->findOrFail($this->studentId);
        $this->subject = Subject::findOrFail($this->subjectId);
        $this->class = ClassModel::findOrFail($this->classId);
    }

    public function getTitle(): string
    {
        return "Input Nilai Tahfidz - {$this->student->user->name}";
    }

    public function getHeading(): string
    {
        return "Input Nilai Tahfidz";
    }

    public function getSubheading(): ?string
    {
        return "{$this->student->nisn} - {$this->student->user->name} | Kelas: {$this->class->name}";
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Surat/Juz')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('arabic_name')
                    ->label('Nama Arab')
                    ->searchable(),

                TextColumn::make('juz')
                    ->label('Juz')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextInputColumn::make('grade')
                    ->label('Nilai')
                    ->rules(['nullable', 'numeric', 'min:0', 'max:100'])
                    ->placeholder('0-100')
                    ->updateStateUsing(function (Model $record, $state) {
                        $this->updateGrade($record, $state);
                    })
                    ->getStateUsing(function (Model $record) {
                        return $this->getCurrentGrade($record);
                    }),
            ])
            ->filters([
                SelectFilter::make('juz')
                    ->label('Filter Juz')
                    ->options(array_combine(range(1, 30), range(1, 30))),
            ])
            ->defaultSort('name', 'asc')
            ->striped()
            ->emptyStateHeading('Tidak ada Tahfidz')
            ->emptyStateDescription('Belum ada tahfidz yang ditambahkan ke kelas ini.');
    }

    protected function getTableQuery(): Builder
    {
        return Tahfidz::query()
            ->whereHas('classModels', function ($query) {
                $query->where('class_models.id', $this->classId);
            });
    }

    protected function getCurrentGrade(Tahfidz $tahfidz): ?int
    {
        $semester = Semester::whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->first();

        if (!$semester) return null;

        $reportCard = ReportCard::where('student_id', $this->studentId)
            ->where('semester_id', $semester->id)
            ->first();

        if (!$reportCard) return null;

        $detail = TahfidzDetail::where('report_card_id', $reportCard->id)
            ->where('tahfidz_id', $tahfidz->id)
            ->first();

        return $detail?->grade;
    }

    protected function updateGrade(Tahfidz $tahfidz, $grade): void
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

        $reportCard = ReportCard::firstOrCreate([
            'student_id' => $this->studentId,
            'semester_id' => $semester->id,
        ]);

        if ($grade === null || $grade === '') {
            TahfidzDetail::where('report_card_id', $reportCard->id)
                ->where('tahfidz_id', $tahfidz->id)
                ->delete();
        } else {
            TahfidzDetail::updateOrCreate(
                [
                    'report_card_id' => $reportCard->id,
                    'tahfidz_id' => $tahfidz->id,
                ],
                [
                    'grade' => $grade,
                ]
            );
        }

        $this->updateAverageGrade($reportCard);

        Notification::make()
            ->success()
            ->title('Nilai berhasil disimpan')
            ->body("Nilai {$tahfidz->name} berhasil diperbarui.")
            ->send();
    }

    protected function updateAverageGrade(ReportCard $reportCard): void
    {
        $tahfidzGrades = TahfidzDetail::where('report_card_id', $reportCard->id)
            ->whereNotNull('grade')
            ->pluck('grade');

        if ($tahfidzGrades->isEmpty()) {
            ReportCardGrade::where('report_card_id', $reportCard->id)
                ->where('subject_id', $this->subjectId)
                ->delete();
        } else {
            $average = round($tahfidzGrades->avg(), 1);

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

    public function getBreadcrumbs(): array
    {
        return [
            SubjectClassResource::getUrl('index', [
                'subject' => $this->subjectId,
                'class' => $this->classId,
            ]) => "Daftar Siswa - {$this->subject->name}",
            '#' => 'Input Nilai Tahfidz',
        ];
    }
}
