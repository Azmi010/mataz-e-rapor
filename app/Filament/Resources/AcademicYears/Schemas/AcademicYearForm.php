<?php

namespace App\Filament\Resources\AcademicYears\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use App\Models\AcademicYear;

class AcademicYearForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Tahun Ajaran')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: 2024/2025'),
                DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->required()
                    ->before('end_date'),
                DatePicker::make('end_date')
                    ->label('Tanggal Selesai')
                    ->required()
                    ->after('start_date'),
                Toggle::make('status')
                    ->label('Status Aktif')
                    ->helperText('Hanya boleh ada 1 tahun ajaran yang aktif')
                    ->default(false),
            ]);
    }
}
