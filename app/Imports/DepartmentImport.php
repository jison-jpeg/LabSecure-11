<?php

namespace App\Imports;

use App\Models\College;
use App\Models\Department;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;

class DepartmentImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    public $failures = []; // Stores detailed error messages for skipped rows
    public $skipped = []; // Tracks skipped records due to duplicates with specific names
    public $successfulImports = 0;

    public function model(array $row)
    {
        // Find the college by name
        $college = College::where('name', $row['college'])->first();

        if (!$college) {
            $this->failures[] = "College '{$row['college']}' not found for department '{$row['name']}'.";
            return null;
        }

        // Check if the department name already exists for this college
        if (Department::where('name', $row['name'])->where('college_id', $college->id)->exists()) {
            $this->skipped[] = "Department '{$row['name']}' under college '{$row['college']}' already exists.";
            return null;
        }

        // Increment successful import count for unique records
        $this->successfulImports++;

        return new Department([
            'name'        => $row['name'],
            'description' => $row['description'],
            'college_id'  => $college->id,
        ]);
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'college'     => 'required|string|max:255', // Reference college by name
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        $this->failures = $failures;
    }
}

