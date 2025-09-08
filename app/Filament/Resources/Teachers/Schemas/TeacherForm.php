<?php

namespace App\Filament\Resources\Teachers\Schemas;

use App\Models\Teacher;
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
                    ->relationship('user', 'name', function($query, $livewire) {
                        $query->where('role', 'teacher');

                        $excludeUserIds = Teacher::pluck('user_id');

                        if ($livewire->record) {
                            $excludeUserIds = $excludeUserIds->reject(fn($id) => $id == $livewire->record->user_id);
                        }

                        $query->whereNotIn('id', $excludeUserIds);
                    })
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
