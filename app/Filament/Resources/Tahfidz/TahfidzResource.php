<?php

namespace App\Filament\Resources\Tahfidz;

use App\Filament\Resources\Tahfidz\Pages\CreateTahfidz;
use App\Filament\Resources\Tahfidz\Pages\EditTahfidz;
use App\Filament\Resources\Tahfidz\Pages\ListTahfidz;
use App\Filament\Resources\Tahfidz\Schemas\TahfidzForm;
use App\Filament\Resources\Tahfidz\Tables\TahfidzTable;
use App\Models\Tahfidz as TahfidzModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TahfidzResource extends Resource
{
    protected static ?string $model = TahfidzModel::class;

    protected static ?string $navigationLabel = 'Tahfidz';

    protected static ?string $pluralModelLabel = 'Tahfidz';

    protected static ?string $modelLabel = 'Tahfidz';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookmark;

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return TahfidzForm::configure($schema);
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Pembelajaran';
    }

    public static function table(Table $table): Table
    {
        return TahfidzTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTahfidz::route('/'),
            'create' => CreateTahfidz::route('/create'),
            'edit' => EditTahfidz::route('/{record}/edit'),
        ];
    }
}
