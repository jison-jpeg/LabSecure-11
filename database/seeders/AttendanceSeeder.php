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
        // Define the date range for the attendance (e.g., last month)
        $startDate = Carbon::now()->subMonth()->startOfMonth();
        $endDate = Carbon::now()->subMonth()->endOfMonth();

        // Retrieve all schedules
        $schedules = Schedule::with(['section', 'instructor'])->get();

        foreach ($schedules as $schedule) {
            // Decode the days of the week for the schedule
            $daysOfWeek = json_decode($schedule->days_of_week, true);

            // Calculate total scheduled minutes for the schedule
            $scheduleStartTime = Carbon::parse($schedule->start_time);
            $scheduleEndTime = Carbon::parse($schedule->end_time);
            $totalScheduledMinutes = $scheduleStartTime->diffInMinutes($scheduleEndTime);

            // Retrieve the instructor for the schedule
            $instructor = $schedule->instructor;

            // Retrieve students belonging to the schedule's section
            $students = User::where('role_id', 2)
                            ->where('section_id', $schedule->section_id)
                            ->get();

            // Combine instructor and students into a single collection
            $users = collect();
            if ($instructor) {
                $users->push($instructor);
            }
            $users = $users->merge($students);

            // Iterate over each day in the date range
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                // Check if the current date's day name matches any of the schedule's days of the week
                if (in_array($date->format('l'), $daysOfWeek)) {
                    foreach ($users as $user) {
                        // Generate a random attendance scenario
                        $scenario = rand(1, 4);

                        if ($scenario === 1) {
                            // Present: On time or within grace period, with attendance >= 75%
                            $timeIn = $scheduleStartTime->copy()->addMinutes(rand(0, 15)); // On time or within 15 min grace
                            $timeOut = $scheduleStartTime->copy()->addMinutes(rand(ceil($totalScheduledMinutes * 0.75), $totalScheduledMinutes));
                        } elseif ($scenario === 2) {
                            // Late: Arrived after 15 minutes but attended >= 75%
                            $timeIn = $scheduleStartTime->copy()->addMinutes(rand(16, 30)); // Arrived late between 16 to 30 minutes
                            $timeOut = $timeIn->copy()->addMinutes(rand(ceil($totalScheduledMinutes * 0.75), $totalScheduledMinutes));
                        } elseif ($scenario === 3) {
                            // Absent: Attended very short duration or late and attended less than required
                            $timeIn = $scheduleStartTime->copy()->addMinutes(rand(20, 40)); // Arrived very late
                            $timeOut = $timeIn->copy()->addMinutes(rand(1, 9)); // Attended less than 10 minutes
                        } elseif ($scenario === 4) {
                            // Incomplete: No time out recorded
                            $timeIn = $scheduleStartTime->copy()->addMinutes(rand(0, 30));
                            $timeOut = null; // No time out recorded
                        }

                        // Create an attendance record without setting the status
                        $attendance = Attendance::create([
                            'user_id' => $user->id,
                            'schedule_id' => $schedule->id,
                            'date' => $date->toDateString(),
                        ]);

                        // Create attendance sessions
                        if ($timeOut) {
                            $attendance->sessions()->create([
                                'time_in' => $timeIn->toDateTimeString(),
                                'time_out' => $timeOut->toDateTimeString(),
                            ]);
                        } else {
                            $attendance->sessions()->create([
                                'time_in' => $timeIn->toDateTimeString(),
                            ]);
                        }

                        // Calculate and save the status and remarks
                        $attendance->calculateAndSaveStatusAndRemarks();
                    }
                }
            }
        }

        $this->command->info("Successfully seeded attendances based on schedules.");
    }
}
