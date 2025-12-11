<?php

namespace App\Filament\Resources\Tahfidz\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TahfidzForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Surat/Juz')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Al-Fatihah, Juz 1')
                    ->columnSpan(1),

                TextInput::make('arabic_name')
                    ->label('Nama Arab')
                    ->maxLength(255)
                    ->placeholder('الفاتحة')
                    ->columnSpan(1),

                TextInput::make('juz')
                    ->label('Nomor Juz')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(30)
                    ->placeholder('1-30')
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->placeholder('Deskripsi atau keterangan tambahan')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
