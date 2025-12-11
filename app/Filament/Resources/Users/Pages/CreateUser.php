<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\Student;
use App\Models\Teacher;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Tambah Akun';

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

    protected function mutateFormDataBeforeCreate(array $data): array
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

    protected function afterCreate(): void
    {
        if ($this->record->role === 'teacher' && isset($this->teacherData)) {
            Teacher::create([
                'user_id' => $this->record->id,
                'nip' => $this->teacherData['nip'] ?? null,
                'phone' => $this->teacherData['phone'] ?? null,
                'address' => $this->teacherData['address'] ?? null,
            ]);
        }

        if ($this->record->role === 'student' && isset($this->studentData)) {
            Student::create([
                'user_id' => $this->record->id,
                'nis' => $this->studentData['nis'] ?? null,
                'class_id' => $this->studentData['class_id'] ?? null,
                'wali' => $this->studentData['wali'] ?? null,
                'phone' => $this->studentData['phone'] ?? null,
                'address' => $this->studentData['address'] ?? null,
            ]);
        }
    }

    public $teacherData = [];
    public $studentData = [];
}
