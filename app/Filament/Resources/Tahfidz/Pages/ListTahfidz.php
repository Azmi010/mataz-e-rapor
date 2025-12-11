<?php

namespace App\Filament\Resources\Tahfidz\Pages;

use App\Filament\Resources\Tahfidz\TahfidzResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTahfidz extends ListRecords
{
    protected static string $resource = TahfidzResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Tambah Tahfidz'),
        ];
    }
}
