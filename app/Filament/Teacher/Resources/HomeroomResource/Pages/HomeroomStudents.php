<?php

namespace App\Filament\Teacher\Resources\HomeroomResource\Pages;

use App\Filament\Teacher\Resources\HomeroomResource;
use Filament\Resources\Pages\Page;

class HomeroomStudents extends Page
{
    protected static string $resource = HomeroomResource::class;

    protected string $view = 'filament.teacher.resources.homeroom.students';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return 'Data Siswa';
    }

    public function getHeading(): string
    {
        return 'Data Siswa Kelas';
    }
}
