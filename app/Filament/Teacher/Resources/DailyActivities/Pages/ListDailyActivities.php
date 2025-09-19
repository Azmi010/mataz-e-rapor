<?php

namespace App\Filament\Teacher\Resources\DailyActivities\Pages;

use App\Filament\Teacher\Resources\DailyActivities\DailyActivityResource;
use Filament\Resources\Pages\ListRecords;

class ListDailyActivities extends ListRecords
{
    protected static string $resource = DailyActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    public function getTitle(): string
    {
        return 'Checklist Aktivitas Harian';
    }

    public function getSubheading(): ?string
    {
        return 'Pilih kelas dan tanggal untuk mulai checklist aktivitas harian siswa';
    }
}
