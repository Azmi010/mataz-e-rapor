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
                    ->required()
                    ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule) {
                        return $rule->where('academic_year_id', $this->ownerRecord->id);
                    }),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->required()
                    ->minDate(fn() => $this->ownerRecord->start_date)
                    ->maxDate(fn() => $this->ownerRecord->end_date)
                    ->before('end_date')
                    ->helperText('Tanggal harus berada dalam rentang tahun ajaran'),
                Forms\Components\DatePicker::make('end_date')
                    ->label('Tanggal Selesai')
                    ->required()
                    ->minDate(fn() => $this->ownerRecord->start_date)
                    ->maxDate(fn() => $this->ownerRecord->end_date)
                    ->after('start_date')
                    ->helperText('Tanggal harus berada dalam rentang tahun ajaran'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('semester_type')
            ->columns([
                Tables\Columns\TextColumn::make('semester_type')
                    ->label('Semester')
                    ->formatStateUsing(fn(string $state): string => "Semester {$state}")
                    ->badge()
                    ->color(fn(string $state): string => $state == '1' ? 'info' : 'success'),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Tanggal Mulai')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Tanggal Selesai')
                    ->date('d M Y'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Semester'),
            ])
            ->actions([
                EditAction::make()
                    ->label('Edit'),
                DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->label('Hapus Terpilih'),

            ]);
    }
}
