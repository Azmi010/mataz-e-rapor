<?php

namespace App\Filament\Teacher\Resources\DailyActivities\Pages;

use App\Filament\Teacher\Resources\DailyActivities\DailyActivityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDailyActivity extends EditRecord
{
    protected static string $resource = DailyActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
