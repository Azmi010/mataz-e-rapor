<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Subject;
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
            'X IPA 1',
            'X IPA 2',
            'XI IPA 1',
            'XII IPA 1',
        ];

        foreach ($classes as $className) {
            $class = ClassModel::create([
                'name' => $className,
                'academic_year_id' => $activeYear->id,
            ]);

            $subjects = Subject::inRandomOrder()->limit(rand(8, 12))->pluck('id');
            $class->subjects()->attach($subjects);
        }

        $students = Student::all();
        $classes = ClassModel::all();

        foreach ($students as $index => $student) {
            $classIndex = $index % $classes->count();
            $student->update(['class_id' => $classes[$classIndex]->id]);
        }
    }
}
