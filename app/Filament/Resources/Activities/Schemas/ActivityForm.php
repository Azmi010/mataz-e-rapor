<?php

namespace App\Filament\Resources\Activities\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ActivityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Aktivitas')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Mengaji, Sholat Berjamaah'),
                Select::make('activity_type')
                    ->label('Tipe Aktivitas')
                    ->options([
                        'Sekolah' => 'Sekolah',
                        'Rumah' => 'Rumah',
                    ])
                    ->required()
                    ->native(false),
            ]);
    }
}
