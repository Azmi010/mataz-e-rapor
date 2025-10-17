<?php

namespace App\Filament\Resources\Subjects\Pages;

use App\Filament\Resources\Subjects\SubjectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubjects extends ListRecords
{
    protected static string $resource = SubjectResource::class;

    protected static ?string $title = 'Data Mata Pelajaran';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Mata Pelajaran'),
        ];
    }
}
