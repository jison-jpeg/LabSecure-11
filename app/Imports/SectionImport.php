<?php

namespace App\Imports;

use App\Models\Section;
use App\Models\College;
use App\Models\Department;
use App\Models\TransactionLog;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;

class SectionImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    public $failures = [];
    public $skipped = [];
    public $successfulImports = 0;

    public function model(array $row)
    {
        // Find college
        $college = College::where('name', $row['college'])->first();
        if (!$college) {
            $this->skipped[] = "College '{$row['college']}' does not exist.";
            return null;
        }

        // Find department within the specified college
        $department = Department::where('name', $row['department'])
                                ->where('college_id', $college->id)
                                ->first();
        if (!$department) {
            $this->skipped[] = "Department '{$row['department']}' does not exist within college '{$row['college']}'.";
            return null;
        }

        // Check for duplicate section
        if (Section::where('name', $row['name'])
            ->where('year_level', $row['year_level'])
            ->where('department_id', $department->id)
            ->exists()) {
            $this->skipped[] = "Section '{$row['name']}' with year level '{$row['year_level']}' already exists in department '{$row['department']}'.";
            return null;
        }

        // Validate semester format (1, 2, summer)
        $validSemesters = ['1', '2', 'summer'];
        if (!in_array(strtolower($row['semester']), $validSemesters)) {
            $this->skipped[] = "Semester '{$row['semester']}' is invalid. Must be '1', '2', or 'summer'.";
            return null;
        }

        // Validate school year format
        if (!preg_match('/^\d{4}-\d{4}$/', $row['school_year'])) {
            $this->skipped[] = "School year '{$row['school_year']}' must be in format YYYY-YYYY.";
            return null;
        }

        // Increment successful imports
        $this->successfulImports++;

        // Create the section
        $section = Section::create([
            'name'          => $row['name'],
            'college_id'    => $college->id,
            'department_id' => $department->id,
            'year_level'    => $row['year_level'],
            'semester'      => strtolower($row['semester']),
            'school_year'   => $row['school_year'],
        ]);

        // Log the transaction
        TransactionLog::create([
            'user_id' => Auth::id(),
            'action'  => 'import',
            'model'   => 'Section',
            'model_id'=> $section->id,
            'details' => json_encode([
                'section_name' => $row['name'],
                'college'      => $row['college'],
                'department'   => $row['department'],
                'year_level'   => $row['year_level'],
                'semester'     => $row['semester'],
                'school_year'  => $row['school_year'],
            ]),
        ]);

        return $section;
    }

    public function rules(): array
    {
        return [
            'name'          => 'required|string|max:255',
            'college'       => 'required|string|max:255',
            'department'    => 'required|string|max:255',
            'year_level'    => 'required|in:1,2,3,4,5,6,irregular',
            'semester'      => 'required|string',
            'school_year'   => 'required|regex:/^\d{4}-\d{4}$/',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        $this->failures = $failures;
    }
}
