<?php

namespace App\Filament\Teacher\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Student;
use App\Models\ClassModel;
use App\Models\DailyActivity;
use App\Models\Attendance;
use App\Models\AcademicYear;

class TeacherStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $activeYear = AcademicYear::where('status', true)->first();
        
        // Total siswa
        $totalStudents = Student::count();
        
        // Total kelas
        $totalClasses = ClassModel::count();
        
        // Kehadiran hari ini
        $todayAttendance = Attendance::whereDate('date', today())->count();
        
        // Aktivitas hari ini
        $todayActivities = DailyActivity::whereDate('activity_date', today())->count();

        return [
            Stat::make('Total Siswa', $totalStudents)
                ->description('Siswa terdaftar')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success')
                ->chart([5, 10, 15, 20, 25, $totalStudents]),

            Stat::make('Total Kelas', $totalClasses)
                ->description('Kelas aktif')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary')
                ->chart([1, 2, 3, 4, $totalClasses]),

            Stat::make('Kehadiran Hari Ini', $todayAttendance)
                ->description('Siswa yang sudah diabsen')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),

            Stat::make('Aktivitas Hari Ini', $todayActivities)
                ->description('Aktivitas yang tercatat')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('warning'),

            Stat::make('Tahun Ajaran', $activeYear ? $activeYear->name : 'Tidak Ada')
                ->description($activeYear ? 'Tahun ajaran aktif' : 'Belum ada tahun ajaran aktif')
                ->descriptionIcon('heroicon-m-calendar')
                ->color($activeYear ? 'success' : 'danger'),
        ];
    }
}
