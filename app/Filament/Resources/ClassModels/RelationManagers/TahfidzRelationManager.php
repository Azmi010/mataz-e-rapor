<?php

namespace App\Filament\Resources\ClassModels\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\BulkActionGroup;

class TahfidzRelationManager extends RelationManager
{
    protected static string $relationship = 'tahfidz';

    protected static ?string $modelLabel = 'Tahfidz';

    protected static ?string $pluralModelLabel = 'Tahfidz';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Surat/Juz')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('arabic_name')
                    ->label('Nama Arab')
                    ->maxLength(255),

                Forms\Components\TextInput::make('juz')
                    ->label('Nomor Juz')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(30),

                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Surat/Juz')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('arabic_name')
                    ->label('Nama Arab')
                    ->searchable(),

                TextColumn::make('juz')
                    ->label('Juz')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('juz')
                    ->label('Filter Juz')
                    ->options(array_combine(range(1, 30), range(1, 30))),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Tambah Tahfidz')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'arabic_name'])
                    ->modalHeading('Tambah Tahfidz ke Kelas'),
            ])
            ->recordActions([
                DetachAction::make()
                    ->label('Hapus')
                    ->icon('heroicon-o-trash'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->label('Hapus yang dipilih'),
                ]),
            ])
            ->emptyStateHeading('Belum ada Tahfidz')
            ->emptyStateDescription('Tambahkan tahfidz yang akan diajarkan di kelas ini.')
            ->emptyStateActions([
                AttachAction::make()
                    ->label('Tambah Tahfidz')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'arabic_name']),
            ]);
    }
}
