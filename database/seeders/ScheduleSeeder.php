<?php

namespace Database\Seeders;

use App\Models\College;
use App\Models\Department;
use App\Models\Laboratory;
use App\Models\Schedule;
use App\Models\Section;
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
        // $subject = Subject::first();
        // $instructor = User::where('role_id', 2)->first();
        // $laboratory = Laboratory::first();
        // $college = College::first();
        // $department = Department::first();
        // $section = Section::first();

        // Fetch all the students
        // $students = User::where(column: 'role_id', 3)->get();

        // Create a schedule for the subject
        // Schedule::create([
        //     'subject_id' => $subject->id,
        //     'instructor_id' => $instructor->id,
        //     'laboratory_id' => $laboratory->id,
        //     'college_id' => $college->id,
        //     'department_id' => $department->id,
        //     'section_id' => $section->id,
        //     'days_of_week' => json_encode(['Monday', 'Wednesday', 'Friday']), // Example for multiple days
        //     'start_time' => '08:00:00',
        //     'end_time' => '10:00:00',
        // ]);
        
    }
}
