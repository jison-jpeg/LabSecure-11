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

class ScheduleSeeder extends Seeder
{
    public function run()
    {
        // Fetch required IDs
        $subjectId = Subject::where('name', 'Capstone Project and Research 3')->first()->id;
        $instructorId = User::where('first_name', 'Sales')
                            ->whereNull('middle_name')
                            ->where('last_name', 'Aribe')
                            ->where('suffix', 'Jr.')
                            ->first()->id;
        $laboratoryId = Laboratory::find(1)->id;
        $collegeId = College::where('name', 'College of Technologies')->first()->id;
        $departmentId = Department::where('name', 'Information Technology')->first()->id;
        $sectionId = Section::where('name', '4A')->first()->id;

        // Create the schedule
        Schedule::create([
            'schedule_code' => 'T100',
            'subject_id' => $subjectId,
            'instructor_id' => $instructorId,
            'laboratory_id' => $laboratoryId,
            'college_id' => $collegeId,
            'department_id' => $departmentId,
            'section_id' => $sectionId,
            'days_of_week' => json_encode(['Monday', 'Thursday']),
            'start_time' => '07:30:00',
            'end_time' => '10:00:00',
        ]);
    }
}
