<?php

namespace App\Filament\Teacher\Widgets;

use Filament\Widgets\Widget;
use App\Models\Attendance;
use App\Models\ClassModel;

class UpcomingActivities extends Widget
{
    protected string $view = 'filament.teacher.widgets.upcoming-activities';
    
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';

    public function getAttendanceStatsProperty()
    {
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        
        $stats = Attendance::whereBetween('date', [$weekStart, $weekEnd])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        return [
            'Hadir' => $stats['Hadir'] ?? 0,
            'Sakit' => $stats['Sakit'] ?? 0,
            'Izin' => $stats['Izin'] ?? 0,
            'Alpha' => $stats['Alpha'] ?? 0,
        ];
    }

    public function getClassesProperty()
    {
        return ClassModel::withCount('students')
            ->orderBy('name')
            ->get();
    }

    public function getWeekStartProperty()
    {
        return now()->startOfWeek()->format('d M');
    }

    public function getWeekEndProperty()
    {
        return now()->endOfWeek()->format('d M Y');
    }
}
