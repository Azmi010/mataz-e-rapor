<?php

namespace App\Filament\Teacher\Resources\GradingResource\Pages;

use App\Filament\Teacher\Resources\GradingResource;
use Filament\Resources\Pages\Page;
use App\Models\Student;

class PreviewReport extends Page
{
    protected static string $resource = GradingResource::class;
    protected string $view = 'filament.teacher.grading.preview-report';

    public Student $record;

    public function mount(Student $record)
    {
        $this->record = $record;
    }

    public function getHeading(): string
    {
        return 'Preview Rapor - ' . ($this->record->user->name ?? 'Siswa');
    }

    public function getTitle(): string
    {
        return 'Preview Rapor - ' . ($this->record->user->name ?? 'Siswa');
    }
}
