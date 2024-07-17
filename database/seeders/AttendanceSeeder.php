<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Subject;
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
        // Retrieve all the users with student role
        $students = User::where('role_id', 3)->get();

        // Retrieve all the users with instructor role
        $instructors = User::where('role_id', 2)->get();

        // Retrieve all subjects
        $subjects = Subject::all();

        // Possible attendance status
        $status = ['Present', 'Absent', 'Late', 'Excused', 'Incomplete'];

        // Loop through all the students
        foreach ($students as $student) {
            // Loop through all the subjects
            foreach ($subjects as $subject) {
                // Create 5 attendance record
                for ($i = 0; $i < 2; $i++) {
                    Attendance::create([
                        'user_id' => $student->id,
                        'subject_id' => $subject->id,
                        'date' => Carbon::now()->subDays($i),
                        'status' => $status[array_rand($status)],
                    ]);
                }
                
            }
        }

        // Loop through all the instructors
        foreach ($instructors as $instructor) {
            // Loop through all the subjects
            foreach ($subjects as $subject) {
                // Create 5 attendance record
                for ($i = 0; $i < 2; $i++) {
                    Attendance::create([
                        'user_id' => $instructor->id,
                        'subject_id' => $subject->id,
                        'date' => Carbon::now()->subDays($i),
                        'status' => $status[array_rand($status)],
                    ]);
                }
                
            }
        }
    }
}
