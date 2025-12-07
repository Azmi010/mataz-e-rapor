<?php

namespace App\Filament\Teacher\Resources\HomeroomResource\Pages;

use App\Filament\Teacher\Resources\HomeroomResource;
use Filament\Resources\Pages\Page;

class HomeroomNotes extends Page
{
    protected static string $resource = HomeroomResource::class;

    protected string $view = 'filament.teacher.resources.homeroom.notes';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return 'Catatan Wali Kelas';
    }

    public function getHeading(): string
    {
        return 'Catatan Wali Kelas';
    }
}
