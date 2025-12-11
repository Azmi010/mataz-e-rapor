<?php

namespace App\Filament\Teacher\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherStatsOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $teacher = Teacher::where('user_id', Auth::id())->first();

        if (!$teacher) {
            return [];
        }

        $classesTeaching = DB::table('class_subjects')
            ->where('teacher_id', $teacher->id)
            ->distinct('class_model_id')
            ->pluck('class_model_id');

        $totalClassesTeaching = $classesTeaching->count();

        $totalStudentsTeaching = Student::whereIn('class_id', $classesTeaching)->count();

        $homeroomClass = ClassModel::where('homeroom_teacher_id', $teacher->id)->first();

        $stats = [
            Stat::make('Siswa yang Diajar', $totalStudentsTeaching)
                ->description('Total siswa di kelas yang diampu')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success')
                ->chart([10, 15, 20, 25, 30, $totalStudentsTeaching]),

            Stat::make('Kelas yang Diajar', $totalClassesTeaching)
                ->description('Kelas yang diampu')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),
        ];

        if ($homeroomClass) {
            $homeroomStudentsCount = Student::where('class_id', $homeroomClass->id)->count();

            $stats[] = Stat::make('Wali Kelas', $homeroomClass->name)
                ->description($homeroomStudentsCount . ' siswa dalam kelas')
                ->descriptionIcon('heroicon-m-home')
                ->color('warning');
        }

        return $stats;
    }
}
