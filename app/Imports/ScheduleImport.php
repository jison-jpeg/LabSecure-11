<?php

namespace App\Imports;

use App\Models\Schedule;
use App\Models\Subject;
use App\Models\User;
use App\Models\College;
use App\Models\Department;
use App\Models\Section;
use App\Models\Laboratory;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;

class ScheduleImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    public $failures = [];
    public $skipped = [];
    public $successfulImports = 0;

    public function model(array $row)
    {
        // Find required entities
        $subject = Subject::where('name', $row['subject'])->first();
        if (!$subject) {
            $this->skipped[] = "Subject '{$row['subject']}' does not exist.";
            return null;
        }

        $instructor = User::where('username', $row['instructor'])->first();
        if (!$instructor) {
            $this->skipped[] = "Instructor with username '{$row['instructor']}' does not exist.";
            return null;
        }

        // Laboratory lookup by ID if numeric, or by name otherwise
        if (is_numeric($row['laboratory'])) {
            $laboratory = Laboratory::find($row['laboratory']);
        } else {
            $laboratory = Laboratory::where('name', $row['laboratory'])->first();
        }
        if (!$laboratory) {
            $this->skipped[] = "Laboratory '{$row['laboratory']}' does not exist.";
            return null;
        }

        $college = College::where('name', $row['college'])->first();
        if (!$college) {
            $this->skipped[] = "College '{$row['college']}' does not exist.";
            return null;
        }

        $department = Department::where('name', $row['department'])
            ->where('college_id', optional($college)->id)
            ->first();
        if (!$department) {
            $this->skipped[] = "Department '{$row['department']}' does not exist within college '{$row['college']}'.";
            return null;
        }

        $section = Section::where('name', $row['section'])
            ->where('year_level', $row['year_level'])
            ->first();
        if (!$section) {
            $this->skipped[] = "Section '{$row['section']}' with year level '{$row['year_level']}' does not exist.";
            return null;
        }

        // Check for duplicate subject in department and section
        if (Schedule::where('subject_id', $subject->id)
            ->where('section_id', $section->id)
            ->where('department_id', $department->id)
            ->exists()
        ) {
            $this->skipped[] = "Schedule for subject '{$row['subject']}' already exists for section '{$row['section']}' and department '{$row['department']}'.";
            return null;
        }

        // Convert days_of_week to an array
        $daysOfWeek = explode(',', $row['days_of_week']);

        // Check for conflicts
        $conflicts = $this->getConflicts($instructor->id, $section->id, $daysOfWeek, $this->convertTo24Hour($row['start_time']), $this->convertTo24Hour($row['end_time']));
        if ($conflicts->isNotEmpty()) {
            $this->skipped[] = "Conflicting schedule for '{$row['subject']}' with instructor '{$row['instructor']}' on given days and time.";
            return null;
        }

        // Determine schedule code
        $schedule_code = !empty($row['schedule_code']) ? $row['schedule_code'] : $this->generateScheduleCode();

        // Increment successful imports
        $this->successfulImports++;

        // Create the schedule
        return new Schedule([
            'subject_id'      => $subject->id,
            'instructor_id'   => $instructor->id,
            'laboratory_id'   => $laboratory->id,
            'college_id'      => $college->id,
            'department_id'   => $department->id,
            'section_id'      => $section->id,
            'schedule_code'   => $schedule_code,
            'days_of_week'    => json_encode($daysOfWeek),
            'start_time'      => $this->convertTo24Hour($row['start_time']),
            'end_time'        => $this->convertTo24Hour($row['end_time']),
        ]);
    }

    /**
     * Convert 12-hour time format (with AM/PM) to 24-hour format.
     */
    protected function convertTo24Hour($time)
    {
        return date("H:i", strtotime($time));
    }


    public function rules(): array
    {
        return [
            'subject'        => 'required|string|max:255',
            'instructor'     => 'required|string|max:255',
            'laboratory'     => 'required',
            'college'        => 'required|string|max:255',
            'department'     => 'required|string|max:255',
            'section'        => 'required|string|max:255',
            'year_level'     => 'required|integer', // Ensure year level is included
            'days_of_week'   => 'required|string',
            'start_time'     => 'required',
            'end_time'       => 'required|after:start_time',
            'schedule_code'  => 'nullable|string|max:255',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        $this->failures = $failures;
    }

    // Generate a unique schedule code if not provided
    protected function generateScheduleCode()
    {
        $lastSchedule = Schedule::orderBy('id', 'desc')->first();
        $newCodeNumber = $lastSchedule ? $lastSchedule->id + 1 + 100 : 100;
        return 'T' . str_pad($newCodeNumber, 3, '0', STR_PAD_LEFT);
    }

    // Function to get conflicting schedules
    protected function getConflicts($instructor_id, $section_id, $daysOfWeek, $start_time, $end_time)
    {
        return Schedule::where(function ($query) use ($instructor_id, $section_id) {
            $query->where('instructor_id', $instructor_id)
                ->orWhere('section_id', $section_id);
        })
            ->where(function ($query) use ($daysOfWeek) {
                foreach ($daysOfWeek as $day) {
                    $query->orWhereJsonContains('days_of_week', trim($day));
                }
            })
            ->where(function ($query) use ($start_time, $end_time) {
                $query->where('start_time', '<', $end_time)
                    ->where('end_time', '>', $start_time);
            })
            ->get();
    }
}
