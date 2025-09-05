<?php

namespace App\Filament\Resources\Subjects\Schemas;

use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;

class SubjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Mata Pelajaran')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Juz 1'),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->placeholder('Deskripsi juz.....')
                    ->columnSpanFull(),

                Toggle::make('has_details')
                    ->label('Memiliki Detail (Komponen)')
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
