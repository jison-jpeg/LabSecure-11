<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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

        // Possible attendance statuses
        $statuses = ['Present', 'Absent', 'Late', 'Excused', 'Incomplete'];

        // Create attendance records
        foreach ($schedules as $schedule) {
            foreach ($users as $user) {
                Attendance::create([
                    'user_id' => $user->id,
                    'schedule_id' => $schedule->id,
                    'time_in' => Carbon::now(),
                    'time_out' => Carbon::now(),
                    'date' => Carbon::now(),
                    'status' => $statuses[array_rand($statuses)],
                ]);
            }
        }
    }
}
