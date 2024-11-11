<?php

namespace App\Imports;

use App\Models\College;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;

class CollegeImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    public $failures = []; // Stores validation errors with row details
    public $skipped = []; // Tracks skipped colleges with specific names
    public $successfulImports = 0;

    public function model(array $row)
    {
        // Manually check for duplicate college name
        if (College::where('name', $row['name'])->exists()) {
            $this->skipped[] = $row['name'];
            return null;
        }

        // Increment successful import count for unique records
        $this->successfulImports++;

        return new College([
            'name'        => $row['name'],
            'description' => $row['description'],
            'created_at'  => $row['created_at'] ?? now(),
            'updated_at'  => $row['updated_at'] ?? now(),
        ]);
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'created_at'  => 'nullable|date',
            'updated_at'  => 'nullable|date',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        $this->failures = $failures;
    }
}
