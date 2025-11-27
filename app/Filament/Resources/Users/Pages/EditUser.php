<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Edit Akun';

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record->role === 'teacher' && $this->record->teacher) {
            $data['teacher'] = [
                'nip' => $this->record->teacher->nip,
                'phone' => $this->record->teacher->phone,
                'address' => $this->record->teacher->address,
            ];
        }

        if ($this->record->role === 'student' && $this->record->student) {
            $data['student'] = [
                'nis' => $this->record->student->nis,
                'class_id' => $this->record->student->class_id,
                'wali' => $this->record->student->wali,
                'phone' => $this->record->student->phone,
                'address' => $this->record->student->address,
            ];
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['teacher'])) {
            $this->teacherData = $data['teacher'];
            unset($data['teacher']);
        }

        if (isset($data['student'])) {
            $this->studentData = $data['student'];
            unset($data['student']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->record->role === 'teacher' && isset($this->teacherData)) {
            $this->record->teacher()->updateOrCreate(
                ['user_id' => $this->record->id],
                [
                    'nip' => $this->teacherData['nip'] ?? null,
                    'phone' => $this->teacherData['phone'] ?? null,
                    'address' => $this->teacherData['address'] ?? null,
                ]
            );
        } elseif ($this->record->role !== 'teacher' && $this->record->teacher) {
            $this->record->teacher()->delete();
        }

        if ($this->record->role === 'student' && isset($this->studentData)) {
            $this->record->student()->updateOrCreate(
                ['user_id' => $this->record->id],
                [
                    'nis' => $this->studentData['nis'] ?? null,
                    'class_id' => $this->studentData['class_id'] ?? null,
                    'wali' => $this->studentData['wali'] ?? null,
                    'phone' => $this->studentData['phone'] ?? null,
                    'address' => $this->studentData['address'] ?? null,
                ]
            );
        } elseif ($this->record->role !== 'student' && $this->record->student) {
            $this->record->student()->delete();
        }
    }

    public $teacherData = [];
    public $studentData = [];

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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
