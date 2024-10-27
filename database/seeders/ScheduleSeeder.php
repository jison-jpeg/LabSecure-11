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
use Carbon\Carbon;

class ScheduleSeeder extends Seeder
{
    /**
     * Predefined non-overlapping time slots.
     *
     * Each time slot is an associative array with 'start' and 'end' keys.
     */
    protected $timeSlots = [
        ['start' => '07:30:00', 'end' => '10:00:00'],
        ['start' => '10:00:00', 'end' => '11:30:00'],
        ['start' => '11:30:00', 'end' => '13:00:00'],
        ['start' => '13:00:00', 'end' => '15:30:00'],
        ['start' => '15:30:00', 'end' => '17:00:00'],
    ];

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

        // Fetch all necessary records with relationships to minimize queries
        $subjects = Subject::with('department')->get(); // Assuming Subject belongs to Department
        $instructors = User::where('role_id', $instructorRole->id)->get();
        $laboratories = Laboratory::all();
        $sections = Section::with(['department.college'])->get(); // Ensure sections have department and college

        // Define fixed day pairs (only pairs of two days)
        $daysOptions = [
            ['Monday', 'Thursday'],
            ['Tuesday', 'Friday'],
            ['Wednesday', 'Saturday'],
            ['Sunday', 'Tuesday'], // Add more pairs as needed
        ];

        // Initialize tracking for assigned days per section
        $sectionDayAssignments = [];

        // Initialize tracking for instructor availability
        // Format: [instructor_id][day] => array of assigned time slots
        $instructorAvailability = [];

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
            // Validate that the section has an associated department and college
            if (!$section->department || !$section->department->college) {
                $this->command->warn("Section ID {$section->id} does not have an associated department or college. Skipping.");
                continue;
            }

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

                // Select a subject that hasn't been assigned to this section yet and belongs to the department
                $availableSubjects = $subjects->filter(function ($subject) use ($section) {
                    return $subject->department_id === $section->department_id &&
                           !Schedule::where('section_id', $section->id)
                                   ->where('subject_id', $subject->id)
                                   ->exists();
                });

                if ($availableSubjects->isEmpty()) {
                    // No available subjects left for this section in this department
                    continue;
                }

                $subject = $availableSubjects->random();

                // Select an instructor and laboratory randomly
                $instructor = $instructors->random();
                $laboratory = $laboratories->random();

                // Derive department and college from the section's relationships
                $department = $section->department;
                $college = $department->college;

                // Attempt to assign a non-conflicting time slot
                $assignedTimeSlot = $this->assignTimeSlot($instructor->id, $dayPair, $this->timeSlots, $instructorAvailability);

                if (!$assignedTimeSlot) {
                    // Could not find a non-conflicting time slot for this instructor on these days
                    $this->command->warn("Could not assign a non-conflicting time slot for Instructor ID {$instructor->id} on days " . implode(', ', $dayPair) . ". Skipping this schedule.");
                    continue;
                }

                // Assign the selected time slot
                $startTime = $assignedTimeSlot['start'];
                $endTime = $assignedTimeSlot['end'];

                // Create the schedule
                Schedule::create([
                    'schedule_code'   => 'T' . $currentNumber++,
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

                // Mark the time slot as assigned for the instructor on these days
                foreach ($dayPair as $day) {
                    $instructorAvailability[$instructor->id][$day][] = $assignedTimeSlot;
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

    /**
     * Assign a non-conflicting time slot for an instructor on specified days.
     *
     * @param int $instructorId
     * @param array $days
     * @param array $timeSlots
     * @param array &$instructorAvailability
     * @param int $maxAttempts
     * @return array|null
     */
    protected function assignTimeSlot(int $instructorId, array $days, array $timeSlots, array &$instructorAvailability, int $maxAttempts = 10): ?array
    {
        // Shuffle time slots to randomize assignment
        $shuffledTimeSlots = collect($timeSlots)->shuffle()->all();

        foreach ($shuffledTimeSlots as $timeSlot) {
            $conflict = false;

            foreach ($days as $day) {
                if (isset($instructorAvailability[$instructorId][$day])) {
                    foreach ($instructorAvailability[$instructorId][$day] as $assignedSlot) {
                        if ($this->timeSlotsOverlap($timeSlot, $assignedSlot)) {
                            $conflict = true;
                            break 2; // Conflict found, no need to check further
                        }
                    }
                }
            }

            if (!$conflict) {
                return $timeSlot; // Found a non-conflicting time slot
            }
        }

        // If no non-conflicting time slot is found after checking all, return null
        return null;
    }

    /**
     * Determine if two time slots overlap.
     *
     * @param array $slot1 ['start' => 'HH:MM:SS', 'end' => 'HH:MM:SS']
     * @param array $slot2 ['start' => 'HH:MM:SS', 'end' => 'HH:MM:SS']
     * @return bool
     */
    protected function timeSlotsOverlap(array $slot1, array $slot2): bool
    {
        $start1 = Carbon::createFromFormat('H:i:s', $slot1['start']);
        $end1 = Carbon::createFromFormat('H:i:s', $slot1['end']);
        $start2 = Carbon::createFromFormat('H:i:s', $slot2['start']);
        $end2 = Carbon::createFromFormat('H:i:s', $slot2['end']);

        return $start1 < $end2 && $start2 < $end1;
    }
}
