<?php

namespace App\Filament\Resources\AcademicYears\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\{CreateAction, EditAction, DeleteAction, BulkActionGroup, DeleteBulkAction};

class SemestersRelationManager extends RelationManager
{
    protected static string $relationship = 'semesters';
    protected static ?string $modelLabel = 'Semester';
    protected static ?string $pluralModelLabel = 'Semester';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('semester_type')
                    ->label('Tipe Semester')
                    ->options([
                        1 => 'Semester 1 (Ganjil)',
                        2 => 'Semester 2 (Genap)',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->label('Tanggal Selesai')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('semester_type')
            ->columns([
                Tables\Columns\TextColumn::make('semester_type')
                    ->label('Semester')
                    ->formatStateUsing(fn(string $state): string => "Semester {$state}"),
                Tables\Columns\TextColumn::make('start_date')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('end_date')
                    ->date('d M Y'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])
            ]);
    }
}
