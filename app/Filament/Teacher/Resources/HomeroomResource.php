<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\HomeroomResource\Pages;
use App\Models\Student;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HomeroomResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'homeroom';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'students' => Pages\HomeroomStudents::route('/students'),
            'attendance' => Pages\HomeroomAttendance::route('/attendance'),
            'notes' => Pages\HomeroomNotes::route('/notes'),
            'achievements' => Pages\HomeroomAchievements::route('/achievements'),
            'reports' => Pages\HomeroomReports::route('/reports'),
        ];
    }
}
