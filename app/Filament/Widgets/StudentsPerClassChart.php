<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\ClassModel;

class StudentsPerClassChart extends ChartWidget
{
    protected ?string $heading = 'Distribusi Siswa per Kelas';
    
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $classes = ClassModel::withCount('students')->get();
        
        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Siswa',
                    'data' => $classes->pluck('students_count')->toArray(),
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.5)',
                        'rgba(16, 185, 129, 0.5)',
                        'rgba(251, 146, 60, 0.5)',
                        'rgba(239, 68, 68, 0.5)',
                        'rgba(139, 92, 246, 0.5)',
                        'rgba(236, 72, 153, 0.5)',
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(251, 146, 60)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)',
                        'rgb(236, 72, 153)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $classes->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
