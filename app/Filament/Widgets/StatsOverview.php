<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\User;
use App\Models\AcademicYear;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $activeYear = AcademicYear::where('status', true)->first();
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalClasses = ClassModel::count();
        $totalSubjects = Subject::count();
        $totalUsers = User::count();

        return [
            Stat::make('Total Siswa', $totalStudents)
                ->description('Siswa terdaftar')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success')
                ->chart([7, 12, 15, 18, 22, 25, $totalStudents]),

            Stat::make('Total Guru', $totalTeachers)
                ->description('Guru aktif')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info')
                ->chart([3, 5, 7, 9, 10, 12, $totalTeachers]),

            Stat::make('Total Kelas', $totalClasses)
                ->description('Kelas tersedia')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('warning')
                ->chart([2, 3, 4, 5, 6, 7, $totalClasses]),

            Stat::make('Total Mata Pelajaran', $totalSubjects)
                ->description('Mata pelajaran')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('primary'),

            Stat::make('Tahun Ajaran Aktif', $activeYear ? $activeYear->name : 'Tidak Ada')
                ->description($activeYear ? 'Tahun ajaran berjalan' : 'Belum ada tahun ajaran aktif')
                ->descriptionIcon('heroicon-m-calendar')
                ->color($activeYear ? 'success' : 'danger'),

            Stat::make('Total Akun', $totalUsers)
                ->description('Pengguna sistem')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
        ];
    }
}
