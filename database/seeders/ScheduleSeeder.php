<?php

namespace Database\Seeders;

use App\Models\Laboratory;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch the first subject, instructor, and laboratory
        $subject = Subject::first();
        $instructor = User::where('role_id', 2)->first();
        $laboratory = Laboratory::first();

        // Fetch all the students
        $students = User::where('role_id', 3)->get();

        // Create a schedule for the subject
        Schedule::create([
            'subject_id' => $subject->id,
            'instructor_id' => $instructor->id,
            'laboratory_id' => $laboratory->id,
            'day_of_week' => 'Monday',
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
        ])->students()->attach($students->pluck('id'));
    }
}
