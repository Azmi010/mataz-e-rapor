<?php

namespace App\Filament\Resources\Students\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use App\Models\Semester;
use App\Models\ReportCard;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('classModel.name')
                    ->label('Kelas')
                    ->sortable()
                    ->default('Belum ada kelas')
                    ->badge()
                    ->color(fn ($state) => $state === 'Belum ada kelas' ? 'gray' : 'success'),
                TextColumn::make('status_nilai')
                    ->label('Nilai')
                    ->badge()
                    ->state(function ($record) {
                        $currentSemester = Semester::whereDate('start_date', '<=', now())
                            ->whereDate('end_date', '>=', now())
                            ->first();

                        if (!$currentSemester) {
                            return 'Tidak ada semester aktif';
                        }

                        $hasReportCard = ReportCard::where('student_id', $record->id)
                            ->where('semester_id', $currentSemester->id)
                            ->exists();

                        return $hasReportCard ? 'Sudah Dinilai' : 'Belum Dinilai';
                    })
                    ->color(fn ($state) => match($state) {
                        'Sudah Dinilai' => 'success',
                        'Belum Dinilai' => 'warning',
                        default => 'gray'
                    }),
            ])
            ->filters([
                SelectFilter::make('class_id')
                    ->label('Kelas')
                    ->relationship('classModel', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus yang dipilih'),
                ]),
            ]);
    }
}
