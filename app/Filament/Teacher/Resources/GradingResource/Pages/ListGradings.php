<?php

namespace App\Filament\Teacher\Resources\GradingResource\Pages;

use App\Filament\Teacher\Resources\GradingResource;
use Filament\Resources\Pages\ListRecords;

class ListGradings extends ListRecords
{
    protected static string $resource = GradingResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'Manajemen Nilai';
    }
}
