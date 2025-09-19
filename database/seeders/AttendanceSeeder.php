<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\Semester;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $students = Student::all();
        $activeSemester = Semester::whereHas('academicYear', function($q) {
            $q->where('status', true);
        })->first();

        if (!$activeSemester) {
            $this->command->info('No active semester found. Skipping attendance seeding.');
            return;
        }

        // Create some sample attendance data for each student
        foreach ($students as $student) {
            $startDate = Carbon::parse($activeSemester->start_date);
            $endDate = min(Carbon::parse($activeSemester->end_date), Carbon::now());

            // Generate attendance for the last 30 days or semester period
            $currentDate = $startDate->copy();
            $attendanceCount = 0;

            while ($currentDate->lte($endDate) && $attendanceCount < 30) {
                // Skip weekends
                if ($currentDate->isWeekday()) {
                    $status = $this->getRandomStatus();

                    Attendance::create([
                        'student_id' => $student->id,
                        'semester_id' => $activeSemester->id,
                        'date' => $currentDate->format('Y-m-d'),
                        'status' => $status,
                    ]);

                    $attendanceCount++;
                }
                $currentDate->addDay();
            }
        }

        $this->command->info('Attendance seeder completed successfully.');
    }

    private function getRandomStatus(): string
    {
        $statuses = ['Hadir', 'Sakit', 'Izin', 'Alpha'];
        $weights = [85, 5, 5, 5];

        $random = rand(1, 100);
        $cumulativeWeight = 0;

        foreach ($weights as $index => $weight) {
            $cumulativeWeight += $weight;
            if ($random <= $cumulativeWeight) {
                return $statuses[$index];
            }
        }

        return 'Hadir';
    }
}
