<?php

namespace App\Filament\Resources\Subjects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Mata Pelajaran')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->default('-')
                    ->badge()
                    ->color('info'),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->toggleable(),

                TextColumn::make('classModels')
                    ->label('Digunakan di Kelas')
                    ->formatStateUsing(function ($record) {
                        $classes = $record->classModels;
                        if ($classes->isEmpty()) {
                            return 'Belum digunakan';
                        }
                        return $classes->pluck('name')->join(', ');
                    })
                    ->wrap()
                    ->badge()
                    ->color(fn($record) => $record->classModels->isEmpty() ? 'gray' : 'success'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus yang dipilih'),
                ]),
            ]);
    }
}
