<?php

namespace App\Filament\Teacher\Widgets;

use Filament\Widgets\Widget;
use App\Models\Teacher;
use App\Models\ClassModel;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherInfoWidget extends Widget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected string $view = 'filament.teacher.widgets.teacher-info';

    public function getTeacherDataProperty()
    {
        $teacher = Teacher::where('user_id', Auth::id())->with('user')->first();

        if (!$teacher) {
            return null;
        }

        $subjects = DB::table('class_subjects')
            ->join('subjects', 'class_subjects.subject_id', '=', 'subjects.id')
            ->where('class_subjects.teacher_id', $teacher->id)
            ->distinct('subjects.id')
            ->pluck('subjects.name')
            ->toArray();

        $homeroomClass = ClassModel::where('homeroom_teacher_id', $teacher->id)->first();

        $activeYear = AcademicYear::where('status', true)->first();

        $currentDate = now()->format('Y-m-d');
        $currentSemester = null;

        if ($activeYear) {
            $currentSemester = Semester::where('academic_year_id', $activeYear->id)
                ->whereDate('start_date', '<=', $currentDate)
                ->whereDate('end_date', '>=', $currentDate)
                ->first();
        }

        return [
            'name' => $teacher->user->name ?? '-',
            'nip' => $teacher->nip ?? '-',
            'subjects' => $subjects,
            'homeroom_class' => $homeroomClass ? $homeroomClass->name : null,
            'academic_year' => $activeYear ? $activeYear->name : '-',
            'semester' => $currentSemester ? $currentSemester->semester_type : '-',
        ];
    }
}
