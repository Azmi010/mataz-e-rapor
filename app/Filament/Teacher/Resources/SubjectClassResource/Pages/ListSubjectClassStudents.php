<?php

namespace App\Filament\Teacher\Resources\SubjectClassResource\Pages;

use App\Filament\Teacher\Resources\SubjectClassResource;
use App\Models\Teacher;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\Student;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class ListSubjectClassStudents extends ListRecords
{
    protected static string $resource = SubjectClassResource::class;

    protected static bool $shouldRegisterNavigation = false;

    public $subjectId;
    public $classId;

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
        return [];
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
