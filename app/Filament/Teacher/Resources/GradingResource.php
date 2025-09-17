<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\GradingResource\Pages;
use App\Models\Student;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Actions\Action;
use App\Models\ClassModel;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class GradingResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationLabel = 'Manajemen Nilai';
    protected static ?string $pluralLabel = 'Manajemen Nilai';
    protected static ?string $slug = 'grading';
    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentDuplicate;

    public static function table(Table $table): Table
    {
        return $table
            ->query(Student::query()->with(['user','classModel']))
            ->columns([
                TextColumn::make('user.name')->label('Nama Siswa')->searchable()->sortable(),
                TextColumn::make('nis')->label('NIS')->searchable()->sortable(),
                TextColumn::make('classModel.name')->label('Kelas'),
            ])
            ->filters([
                Filter::make('class_filter')
                    ->label('Filter Kelas')
                    ->form([
                        Select::make('class_id')
                            ->label('Pilih Kelas')
                            ->placeholder('Pilih kelas...')
                            ->options(ClassModel::query()
                                ->whereHas('academicYear', fn($q) => $q->where('status', true))
                                ->pluck('name','id'))
                            ->live()
                            ->required(),
                    ])
                    ->query(fn(Builder $query, array $data) => $query->when($data['class_id'] ?? null, fn($q,$v)=>$q->where('class_id',$v))),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->recordActions([
                Action::make('fillGrades')
                    ->label('Isi Nilai')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->url(fn($record) => Pages\FillGrades::getUrl(['record' => $record->getKey()])),
                Action::make('previewReport')
                    ->label('Lihat Rapor')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->url(fn($record) => Pages\PreviewReport::getUrl(['record' => $record->getKey()])),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->defaultSort('user.name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGradings::route('/'),
            'fill' => Pages\FillGrades::route('/{record}/isi-nilai'),
            'preview' => Pages\PreviewReport::route('/{record}/rapor'),
        ];
    }
}
