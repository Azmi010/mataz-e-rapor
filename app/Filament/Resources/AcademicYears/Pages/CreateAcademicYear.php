<?php

namespace App\Filament\Resources\AcademicYears\Pages;

use App\Filament\Resources\AcademicYears\AcademicYearResource;
use App\Models\AcademicYear;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAcademicYear extends CreateRecord
{
    protected static string $resource = AcademicYearResource::class;

    protected static ?string $title = 'Tambah Tahun Ajaran';

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

    protected function handleRecordCreation(array $data): Model
    {
        if ($data['status']) {
            AcademicYear::where('status', true)->update(['status' => false]);
        }

        return static::getModel()::create($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
