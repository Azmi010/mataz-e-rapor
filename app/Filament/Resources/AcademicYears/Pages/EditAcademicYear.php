<?php

namespace App\Filament\Resources\AcademicYears\Pages;

use App\Filament\Resources\AcademicYears\AcademicYearResource;
use App\Models\AcademicYear;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditAcademicYear extends EditRecord
{
    protected static string $resource = AcademicYearResource::class;

    protected static ?string $title = 'Edit Tahun Ajaran';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Hapus'),
        ];
    }

    protected function getSaveFormAction(): \Filament\Actions\Action
    {
        return parent::getSaveFormAction()
            ->label('Simpan');
    }

    protected function getCancelFormAction(): \Filament\Actions\Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if ($data['status']) {
            AcademicYear::where('id', '!=', $record->id)
                ->where('status', true)
                ->update(['status' => false]);
        }

        $record->update($data);
        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
