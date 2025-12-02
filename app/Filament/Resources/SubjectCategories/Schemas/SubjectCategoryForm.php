<?php

namespace App\Filament\Resources\SubjectCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SubjectCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Kategori')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Pendidikan Agama Islam, Matematika, IPA')
                    ->helperText('Nama kategori mata pelajaran')
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->placeholder('Deskripsi kategori mata pelajaran')
                    ->columnSpanFull(),
            ]);
    }
}
