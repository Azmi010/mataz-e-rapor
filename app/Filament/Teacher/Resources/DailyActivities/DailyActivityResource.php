<?php

namespace App\Filament\Teacher\Resources\DailyActivities;

use App\Filament\Teacher\Resources\DailyActivities\Pages\CreateDailyActivity;
use App\Filament\Teacher\Resources\DailyActivities\Pages\EditDailyActivity;
use App\Filament\Teacher\Resources\DailyActivities\Pages\ListDailyActivities;
use App\Filament\Teacher\Resources\DailyActivities\Schemas\DailyActivityForm;
use App\Filament\Teacher\Resources\DailyActivities\Tables\DailyActivitiesTable;
use App\Models\DailyActivity;
use App\Models\Student;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DailyActivityResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationLabel = 'Checklist Aktivitas Harian';
    protected static ?string $modelLabel = 'Aktivitas Harian';
    protected static ?string $pluralModelLabel = 'Checklist Aktivitas Harian';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return DailyActivityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DailyActivitiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDailyActivities::route('/'),
        ];
    }
}
