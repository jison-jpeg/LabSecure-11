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
            foreach ($users as $user) {
                // Generate a random date within the past month
                $randomDate = Carbon::now()->subDays(rand(0, 30));

                // Generate random time_in and time_out within the same day
                $timeIn = $randomDate->copy()->setTime(rand(7, 10), rand(0, 59)); // Time between 7:00 AM and 10:59 AM
                $timeOut = $timeIn->copy()->addHours(rand(1, 8))->addMinutes(rand(0, 59)); // Random time between 1 to 8 hours after time_in

                // Create an attendance record without setting the status
                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'schedule_id' => $schedule->id,
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'date' => $randomDate->toDateString(),
                ]);

                // Calculate and save the status and remarks
                $attendance->calculateAndSaveStatusAndRemarks();
            }
        }
    }
}
