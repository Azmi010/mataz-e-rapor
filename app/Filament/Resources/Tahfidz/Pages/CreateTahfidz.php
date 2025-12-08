<?php

namespace App\Filament\Resources\Tahfidz\Pages;

use App\Filament\Resources\Tahfidz\TahfidzResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTahfidz extends CreateRecord
{
    protected static string $resource = TahfidzResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
