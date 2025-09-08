<?php

namespace App\Filament\Resources\Subjects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->toggleable(),

                IconColumn::make('has_details')
                    ->label('Memiliki Detail')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('details_count')
                    ->label('Jumlah Detail')
                    ->counts('details')
                    ->badge()
                    ->color('info')
                    ->visible(fn($record) => $record?->has_details),

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
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ]);
    }
}
