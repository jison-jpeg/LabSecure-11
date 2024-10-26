<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\User;
use App\Models\Laboratory;
use App\Models\College;
use App\Models\Department;
use App\Models\Section;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Fetch the 'instructor' role
        $instructorRole = Role::where('name', 'instructor')->first();

        if (!$instructorRole) {
            $this->command->error("Role 'instructor' not found. Please seed roles first.");
            return;
        }

        // Ensure related tables have data
        if (
            Subject::count() === 0 ||
            User::where('role_id', $instructorRole->id)->count() === 0 ||
            Laboratory::count() === 0 ||
            College::count() === 0 ||
            Department::count() === 0 ||
            Section::count() === 0
        ) {
            $this->command->error('One or more related tables are empty. Please seed them first.');
            return;
        }

        // Fetch all necessary records
        $subjects = Subject::all();
        $instructors = User::where('role_id', $instructorRole->id)->get();
        $laboratories = Laboratory::all();
        $colleges = College::all();
        $departments = Department::all();
        $sections = Section::all();

        // Define fixed day pairs (only pairs of two days)
        $daysOptions = [
            ['Monday', 'Thursday'],
            ['Tuesday', 'Friday'],
            ['Wednesday', 'Saturday'],
            ['Sunday', 'Tuesday'], // Add more pairs as needed
        ];

        $startTimes = ['07:30:00', '09:00:00', '10:30:00', '13:00:00', '14:30:00'];
        $endTimes = ['10:00:00', '11:30:00', '13:00:00', '15:30:00', '17:00:00'];

        // Initialize tracking for assigned days per section
        $sectionDayAssignments = [];

        // Define how many schedules you want to create
        // Adjust based on number of sections and day pairs
        // For example, if you have 10 sections and 4 day pairs, max schedules = 40
        $numberOfSchedules = 50;

        // Determine the starting schedule code number
        $latestSchedule = Schedule::where('schedule_code', 'like', 'T%')
                            ->orderBy('schedule_code', 'desc')
                            ->first();

        if ($latestSchedule) {
            // Extract the numeric part from the latest schedule_code
            $latestNumber = (int) substr($latestSchedule->schedule_code, 1);
            $startNumber = $latestNumber + 1;
        } else {
            // Start from T100 if no existing schedules
            $startNumber = 100;
        }

        $currentNumber = $startNumber;

        // Shuffle sections and day pairs for randomness
        $shuffledSections = $sections->shuffle();
        $shuffledDaysOptions = collect($daysOptions)->shuffle();

        foreach ($shuffledSections as $section) {
            // Initialize assigned days for the section
            if (!isset($sectionDayAssignments[$section->id])) {
                $sectionDayAssignments[$section->id] = [];
            }

            foreach ($shuffledDaysOptions as $dayPair) {
                // Check if the days in the pair are already assigned to the section
                $overlap = false;
                foreach ($dayPair as $day) {
                    if (in_array($day, $sectionDayAssignments[$section->id])) {
                        $overlap = true;
                        break;
                    }
                }

                if ($overlap) {
                    // Skip this day pair as it overlaps with already assigned days
                    continue;
                }

                // Select a subject that hasn't been assigned to this section yet
                $availableSubjects = $subjects->filter(function ($subject) use ($section) {
                    return !Schedule::where('section_id', $section->id)
                                   ->where('subject_id', $subject->id)
                                   ->exists();
                });

                if ($availableSubjects->isEmpty()) {
                    // No available subjects left for this section
                    continue;
                }

                $subject = $availableSubjects->random();

                // Select an instructor, laboratory, college, department randomly
                $instructor = $instructors->random();
                $laboratory = $laboratories->random();
                $college = $colleges->random();
                $department = $departments->random();

                // Assign a sequential schedule code
                $scheduleCode = 'T' . $currentNumber++;

                // Randomly select start and end times
                $startTime = $startTimes[array_rand($startTimes)];
                $endTime = $endTimes[array_rand($endTimes)];

                // Ensure that end time is after start time
                if ($endTime <= $startTime) {
                    // Swap times if end time is not after start time
                    [$startTime, $endTime] = [$endTime, $startTime];
                }

                // Create the schedule
                Schedule::create([
                    'schedule_code'   => $scheduleCode,
                    'subject_id'      => $subject->id,
                    'instructor_id'   => $instructor->id,
                    'laboratory_id'   => $laboratory->id,
                    'college_id'      => $college->id,
                    'department_id'   => $department->id,
                    'section_id'      => $section->id,
                    'days_of_week'    => json_encode($dayPair),
                    'start_time'      => $startTime,
                    'end_time'        => $endTime,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                // Mark the days as assigned for the section
                foreach ($dayPair as $day) {
                    $sectionDayAssignments[$section->id][] = $day;
                }

                // Increment the schedule count
                $numberOfSchedules--;

                // Stop if we've reached the desired number of schedules
                if ($numberOfSchedules <= 0) {
                    break 2; // Exit both foreach loops
                }
            }
        }

        $this->command->info("Successfully seeded schedules starting from T{$startNumber}.");
    }
}
