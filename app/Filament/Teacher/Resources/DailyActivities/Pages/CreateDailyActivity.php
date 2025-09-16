<?php

namespace App\Filament\Teacher\Resources\DailyActivities\Pages;

use App\Filament\Teacher\Resources\DailyActivities\DailyActivityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDailyActivity extends CreateRecord
{
    protected static string $resource = DailyActivityResource::class;
}
