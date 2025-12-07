<?php

namespace App\Filament\Teacher\Resources\HomeroomResource\Pages;

use App\Filament\Teacher\Resources\HomeroomResource;
use Filament\Resources\Pages\Page;

class HomeroomAttendance extends Page
{
    protected static string $resource = HomeroomResource::class;

    protected string $view = 'filament.teacher.resources.homeroom.attendance';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return 'Absen Siswa';
    }

    public function getHeading(): string
    {
        return 'Absen Siswa';
    }
}
