<?php

namespace App\Filament\Teacher\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Attendance;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return 'Statistik Kehadiran Minggu Ini';
    }

    protected function getData(): array
    {
        $teacher = Teacher::where('user_id', Auth::id())->first();

        if (!$teacher) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $classIds = DB::table('class_subjects')
            ->where('teacher_id', $teacher->id)
            ->distinct('class_model_id')
            ->pluck('class_model_id');

        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        $attendanceData = Attendance::whereBetween('date', [$weekStart, $weekEnd])
            ->whereHas('student', function ($query) use ($classIds) {
                $query->whereIn('class_id', $classIds);
            })
            ->get()
            ->groupBy(function($item) {
                return $item->date->format('Y-m-d');
            })
            ->map(function($items) {
                return $items->groupBy('status')->map(function($group) {
                    return $group->count();
                });
            });

        $labels = [];
        $hadirData = [];
        $sakitData = [];
        $izinData = [];
        $alphaData = [];

        $dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i);
            $dayOfWeek = $date->dayOfWeek;
            $month = $monthNames[$date->month - 1];
            $labels[] = $dayNames[$dayOfWeek] . ', ' . $date->format('d') . ' ' . $month;

            $dateKey = $date->format('Y-m-d');
            $dayData = $attendanceData->get($dateKey, collect());

            $hadirData[] = $dayData->get('Hadir', 0);
            $sakitData[] = $dayData->get('Sakit', 0);
            $izinData[] = $dayData->get('Izin', 0);
            $alphaData[] = $dayData->get('Alpha', 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Hadir',
                    'data' => $hadirData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.5)',
                    'borderColor' => 'rgb(34, 197, 94)',
                ],
                [
                    'label' => 'Sakit',
                    'data' => $sakitData,
                    'backgroundColor' => 'rgba(250, 204, 21, 0.5)',
                    'borderColor' => 'rgb(250, 204, 21)',
                ],
                [
                    'label' => 'Izin',
                    'data' => $izinData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
                [
                    'label' => 'Alpha',
                    'data' => $alphaData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.5)',
                    'borderColor' => 'rgb(239, 68, 68)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
