<?php

namespace App\Filament\Resources\Subjects\Schemas;

use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use App\Models\Subject;
use App\Models\SubjectCategory;

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

                Toggle::make('is_tahfidz')
                    ->label('Termasuk Mata Pelajaran Tahfidz?')
                    ->helperText('Aktifkan jika nilai mata pelajaran ini akan dihitung ke rata-rata tahfidz')
                    ->reactive()
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->placeholder('Deskripsi mata pelajaran')
                    ->columnSpanFull(),

                Toggle::make('has_details')
                    ->label('Memiliki Detail Surat')
                    ->helperText('Aktifkan jika mata pelajaran memiliki komponen detail seperti surat dalam juz')
                    ->reactive()
                    ->columnSpanFull(),

                Repeater::make('details')
                    ->relationship('details')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Surat')
                            ->required()
                            ->placeholder('Contoh: Al-Fatihah')
                            ->maxLength(255),

                        Textarea::make('description')
                            ->label('Deskripsi Surat')
                            ->placeholder('Contoh: Surat pembuka Al-Quran')
                            ->rows(2),

                        TextInput::make('order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(1)
                            ->required(),
                    ])
                    ->columns(3)
                    ->orderColumn('order')
                    ->addActionLabel('Tambah Detail')
                    ->deleteAction(function ($action) {
                        return $action->requiresConfirmation();
                    })
                    ->minItems(0)
                    ->defaultItems(0)
                    ->collapsed()
                    ->cloneable()
                    ->itemLabel(fn (array $state): ?string =>
                        ($state['order'] ?? '') . '. ' . ($state['name'] ?? 'Detail Baru')
                    )
                    ->visible(fn (callable $get): bool => $get('has_details'))
                    ->columnSpanFull(),
            ]);
    }
}
