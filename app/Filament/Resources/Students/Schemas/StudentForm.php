<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Models\Student;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Pilih User')
                    ->relationship('user', 'name', function($query, $livewire) {
                        $query->where('role', 'student');

                        $excludeUserIds = Student::pluck('user_id');

                        if ($livewire->record) {
                            $excludeUserIds = $excludeUserIds->reject(fn($id) => $id == $livewire->record->user_id);
                        }

                        $query->whereNotIn('id', $excludeUserIds);
                    })
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name} ({$record->email})")
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('class_id')
                    ->label('Kelas')
                    ->relationship('classModel', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                TextInput::make('nis')
                    ->label('NIS (Nomor Induk Siswa)')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true),
                TextInput::make('wali')
                    ->label('Nama Wali')
                    ->maxLength(255)
                    ->nullable(),
            ]);
    }
}
