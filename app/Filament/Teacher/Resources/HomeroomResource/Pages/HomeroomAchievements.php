<?php

namespace App\Filament\Teacher\Resources\HomeroomResource\Pages;

use App\Filament\Teacher\Resources\HomeroomResource;
use Filament\Resources\Pages\Page;

class HomeroomAchievements extends Page
{
    protected static string $resource = HomeroomResource::class;

    protected string $view = 'filament.teacher.resources.homeroom.achievements';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return 'Prestasi Siswa';
    }

    public function getHeading(): string
    {
        return 'Prestasi Siswa';
    }
}
