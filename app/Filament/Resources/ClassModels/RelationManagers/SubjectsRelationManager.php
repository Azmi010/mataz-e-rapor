<?php

namespace App\Filament\Resources\ClassModels\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\{CreateAction, EditAction, DeleteAction, BulkActionGroup, DeleteBulkAction, AttachAction, DetachBulkAction, DetachAction};

class SubjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'subjects';
    protected static ?string $modelLabel = 'Mata Pelajaran';
    protected static ?string $pluralModelLabel = 'Mata Pelajaran';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Mata Pelajaran')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('has_details')
                    ->label('Memiliki Detail')
                    ->helperText('Apakah mata pelajaran ini memiliki detail tambahan?')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Mata Pelajaran')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('has_details')
                    ->label('Memiliki Detail')
                    ->badge()
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Ya' : 'Tidak')
                    ->color(fn(bool $state): string => $state ? 'success' : 'gray'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Tambah Mata Pelajaran')
                    ->preloadRecordSelect(),
            ])
            ->actions([
                EditAction::make()
                    ->label('Edit'),
                DetachAction::make()
                    ->label('Hapus'),
            ])
            ->bulkActions([
                DetachBulkAction::make()
                    ->label('Hapus Terpilih'),
            ]);
    }
}
