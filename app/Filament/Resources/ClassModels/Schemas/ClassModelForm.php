<?php

namespace App\Filament\Resources\ClassModels\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ClassModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Kelas')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: XII IPA 1'),
                Select::make('academic_year_id')
                    ->label('Tahun Akademik')
                    ->relationship('academicYear', 'name')
                    ->required()
                    ->searchable(),
                Select::make('subjects')
                    ->label('Mata Pelajaran')
                    ->relationship('subjects', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->helperText('Pilih mata pelajaran yang diajarkan di kelas ini'),
            ]);
    }
}
