<?php

namespace App\Filament\Teacher\Resources\HomeroomResource\Pages;

use App\Filament\Teacher\Resources\HomeroomResource;
use Filament\Resources\Pages\Page;

class HomeroomReports extends Page
{
    protected static string $resource = HomeroomResource::class;

    protected string $view = 'filament.teacher.resources.homeroom.reports';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return 'Rapor Siswa';
    }

    public function getHeading(): string
    {
        return 'Rapor Siswa';
    }
}
