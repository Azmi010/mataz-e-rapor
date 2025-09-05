<?php

namespace App\Filament\Resources\Teachers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TeacherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Pilih User')
                    ->relationship('user', 'name', fn($query) => $query->where('role', 'teacher'))
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name} ({$record->email})")
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('nip')
                    ->label('NIP (Nomor Induk Pegawai)')
                    ->required()
                    ->maxLength(30)
                    ->unique(ignoreRecord: true),
            ]);
    }
}
