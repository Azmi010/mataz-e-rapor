<?php

namespace App\Filament\Teacher\Widgets;

use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;
use App\Models\Student;

class RecentStudents extends TableWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Student::query()
                    ->with(['user', 'classModel'])
                    ->latest()
                    ->limit(10)
            )
            ->heading('Siswa Terdaftar Terbaru')
            ->columns([
                TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('classModel.name')
                    ->label('Kelas')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Telepon')
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->label('Tanggal Daftar')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
