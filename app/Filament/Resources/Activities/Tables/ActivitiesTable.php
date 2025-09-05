<?php

namespace App\Filament\Resources\Activities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Aktivitas')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('activity_type')
                    ->label('Tipe Aktivitas')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Sekolah' => 'success',
                        'Rumah' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('dailyActivities_count')
                    ->label('Total Pencatatan')
                    ->counts('dailyActivities')
                    ->badge()
                    ->color('warning'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('activity_type')
                    ->label('Tipe Aktivitas')
                    ->options([
                        'Sekolah' => 'Sekolah',
                        'Rumah' => 'Rumah',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
