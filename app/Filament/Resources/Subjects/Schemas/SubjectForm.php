<?php

namespace App\Filament\Resources\Subjects\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class SubjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label('Kategori Mata Pelajaran')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText('Pilih kategori untuk mata pelajaran ini')
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Nama Kategori')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3),
                    ])
                    ->columnSpanFull(),

                TextInput::make('name')
                    ->label('Nama Mata Pelajaran')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Matematika, Bahasa Indonesia')
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->placeholder('Deskripsi mata pelajaran')
                    ->columnSpanFull(),
            ]);
    }
}
