<?php

namespace App\Filament\Resources\ClassModels\Pages;

use App\Filament\Resources\ClassModels\ClassModelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateClassModel extends CreateRecord
{
    protected static string $resource = ClassModelResource::class;

    protected static ?string $title = 'Tambah Kelas';

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Tambah');
    }

    protected function getCreateAnotherFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Tambah & Tambah Lainnya');
    }

    protected function getCancelFormAction(): \Filament\Actions\Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
