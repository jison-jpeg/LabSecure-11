<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Retrieve all users with student and instructor roles
        $users = User::where('role_id', 2)->orWhere('role_id', 3)->get();

        // Retrieve all schedules
        $schedules = Schedule::all();

        // Create attendance records
        foreach ($schedules as $schedule) {
            // Decode the days of the week for the schedule
            $daysOfWeek = json_decode($schedule->days_of_week, true);

            // Define the date range for the attendance (e.g., last month)
            $startDate = Carbon::now()->subMonth()->startOfMonth();
            $endDate = Carbon::now()->subMonth()->endOfMonth();

            // Iterate over each day in the range
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                // Check if the current date's day name matches any of the schedule's days of the week
                if (in_array($date->format('l'), $daysOfWeek)) {
                    foreach ($users as $user) {
                        // Skip if user does not belong to the schedule's section or instructor is not assigned
                        if ($user->isStudent() && $user->section_id !== $schedule->section_id) {
                            continue;
                        }
                        if ($user->isInstructor() && $user->id !== $schedule->instructor_id) {
                            continue;
                        }

                        // Generate the time_in based on the schedule's start time, adding 1 to 30 minutes
                        $startTime = Carbon::parse($schedule->start_time);
                        $timeIn = $startTime->copy()->addMinutes(rand(1, 30));

                        // Generate the time_out based on the time_in, adding another 1 to 30 minutes
                        $timeOut = $timeIn->copy()->addMinutes(rand(1, 30));

                        // Create an attendance record without setting the status
                        $attendance = Attendance::create([
                            'user_id' => $user->id,
                            'schedule_id' => $schedule->id,
                            'date' => $date->toDateString(),
                        ]);

                        // Create attendance sessions
                        $attendance->sessions()->create([
                            'time_in' => $timeIn->toDateTimeString(),
                            'time_out' => $timeOut->toDateTimeString(),
                        ]);

                        // Calculate and save the status and remarks
                        $attendance->calculateAndSaveStatusAndRemarks();
                    }
                }
            }
        }
    }
}
