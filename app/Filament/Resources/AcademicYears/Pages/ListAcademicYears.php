<?php

namespace App\Filament\Resources\AcademicYears\Pages;

use App\Filament\Resources\AcademicYears\AcademicYearResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAcademicYears extends ListRecords
{
    protected static string $resource = AcademicYearResource::class;

    protected static ?string $title = 'Daftar Tahun Ajaran';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Tahun Ajaran'),
        ];
    }
}
