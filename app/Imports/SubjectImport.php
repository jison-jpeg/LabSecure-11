<?php

namespace App\Imports;

use App\Models\College;
use App\Models\Department;
use App\Models\Subject;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
class SubjectImport implements ToModel, WithHeadingRow, SkipsOnFailure, WithValidation
{
    public $failures = [];
    public $skipped = []; // To keep track of skipped records
    public $successfulImports = 0; // To count successful imports

    public function model(array $row)
    {
        // Check if the subject already exists
        $existingSubject = Subject::where('name', $row['name'])
                                  ->where('code', $row['code'])
                                  ->first();

        if ($existingSubject) {
            // If exists, skip this row and add to skipped list
            $this->skipped[] = [
                'name' => $row['name'],
                'code' => $row['code']
            ];
            return null;
        }

        // Increment successful import count for valid new records
        $this->successfulImports++;

        // Look up IDs for college and department by name
        $college = College::where('name', $row['college'])->first();
        $department = Department::where('name', $row['department'])->where('college_id', $college->id)->first();

        return new Subject([
            'name'          => $row['name'],
            'code'          => $row['code'],
            'description'   => $row['description'],
            'college_id'    => $college->id,
            'department_id' => $department->id,
            'created_at'    => $row['created_at'] ?? now(),
            'updated_at'    => $row['updated_at'] ?? now(),
        ]);
    }

    public function rules(): array
    {
        return [
            'name'       => 'required|string|max:255',
            'code'       => 'required|string|max:255',
            'description'=> 'nullable|string',
            'college'    => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'created_at' => 'nullable|date',
            'updated_at' => 'nullable|date',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        $this->failures = $failures;
    }
}