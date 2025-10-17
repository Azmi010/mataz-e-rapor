<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Student;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activeYear = AcademicYear::where('status', true)->first();

        if (!$activeYear) {
            return;
        }

        $classes = [
            'Kelas 1',
            'Kelas 2',
            'Kelas 3',
            'Kelas 4',
        ];

        foreach ($classes as $className) {
            ClassModel::create([
                'name' => $className,
                'academic_year_id' => $activeYear->id,
            ]);
        }

        $students = Student::all();
        $classes = ClassModel::all();

        foreach ($students as $index => $student) {
            $classIndex = $index % $classes->count();
            $student->update(['class_id' => $classes[$classIndex]->id]);
        }
    }
}
