<?php

namespace App\Filament\Resources\ClassModels\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\{CreateAction, EditAction, DeleteAction, BulkActionGroup, DeleteBulkAction, AttachAction, DetachBulkAction};

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';
    protected static ?string $modelLabel = 'Siswa';
    protected static ?string $pluralModelLabel = 'Siswa';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Pilih Siswa')
                    ->relationship('user', 'name', fn($query) => $query->where('role', 'student'))
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name} ({$record->email})")
                    ->required()
                    ->preload()
                    ->searchable(),
                Forms\Components\TextInput::make('nis')
                    ->label('NIS')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('wali')
                    ->label('Nama Wali')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nis')
            ->columns([
                TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.profile.full_name')
                    ->label('Nama Lengkap')
                    ->default(fn($record) => $record->user?->name ?? 'N/A')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('wali')
                    ->label('Wali')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Ditambahkan')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Siswa'),
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
