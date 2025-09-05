<?php

namespace App\Filament\Resources\Students\Schemas;

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
                    ->relationship('user', 'name', fn($query) => $query->where('role', 'student'))
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
