<?php

namespace App\Filament\Resources\SubjectCategories;

use App\Filament\Resources\SubjectCategories\Pages\CreateSubjectCategory;
use App\Filament\Resources\SubjectCategories\Pages\EditSubjectCategory;
use App\Filament\Resources\SubjectCategories\Pages\ListSubjectCategories;
use App\Filament\Resources\SubjectCategories\Schemas\SubjectCategoryForm;
use App\Filament\Resources\SubjectCategories\Tables\SubjectCategoriesTable;
use App\Models\SubjectCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SubjectCategoryResource extends Resource
{
    protected static ?string $model = SubjectCategory::class;

    protected static ?string $navigationLabel = 'Kategori Mata Pelajaran';

    protected static ?string $pluralModelLabel = 'Kategori Mata Pelajaran';

    protected static ?string $modelLabel = 'Kategori Mata Pelajaran';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return SubjectCategoryForm::configure($schema);
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Pembelajaran';
    }

    public static function table(Table $table): Table
    {
        return SubjectCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubjectCategories::route('/'),
            'create' => CreateSubjectCategory::route('/create'),
            'edit' => EditSubjectCategory::route('/{record}/edit'),
        ];
    }
}
