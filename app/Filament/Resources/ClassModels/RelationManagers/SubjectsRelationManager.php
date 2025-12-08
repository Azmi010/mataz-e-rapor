<?php

namespace App\Filament\Resources\ClassModels\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Action;
use App\Models\Teacher;

class SubjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'subjects';
    protected static ?string $modelLabel = 'Mata Pelajaran';
    protected static ?string $pluralModelLabel = 'Mata Pelajaran';
    protected static ?string $title = 'Mata Pelajaran';

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
                TextColumn::make('pivot.teacher_id')
                    ->label('Guru Pengajar')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '-';
                        $teacher = Teacher::with('user')->find($state);
                        return $teacher ? $teacher->user->name : '-';
                    })
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
                    ->preloadRecordSelect()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\Select::make('teacher_id')
                            ->label('Guru Pengajar')
                            ->options(Teacher::with('user')->get()->pluck('user.name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Pilih guru yang mengajar mata pelajaran ini di kelas ini'),
                    ]),
            ])
            ->recordActions([
                Action::make('edit_teacher')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->form([
                        Forms\Components\Select::make('teacher_id')
                            ->label('Guru Pengajar')
                            ->options(Teacher::with('user')->get()->pluck('user.name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Pilih guru yang mengajar mata pelajaran ini di kelas ini')
                            ->default(fn ($record) => $record->pivot->teacher_id),
                    ])
                    ->action(function (array $data, $record): void {
                        $record->pivot->update([
                            'teacher_id' => $data['teacher_id'],
                        ]);
                    })
                    ->modalHeading('Edit Guru Pengajar')
                    ->modalSubmitActionLabel('Simpan'),
                DetachAction::make()
                    ->label('Hapus')
                    ->icon('heroicon-o-trash'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ]);
    }
}
