<?php

namespace App\Imports;

use App\Models\College;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;

class FacultyImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    public $failures = []; // Store validation failures with row details
    public $skipped = []; // Track skipped records with specific messages
    public $successfulImports = 0;

    public function model(array $row)
    {
        // Check for duplicate username or email
        if (User::where('username', $row['username'])->exists()) {
            $this->skipped[] = "Username '{$row['username']}' already exists.";
            return null;
        }

        if (User::where('email', $row['email'])->exists()) {
            $this->skipped[] = "Email '{$row['email']}' already exists.";
            return null;
        }

        // Check if college exists
        $college = College::where('name', $row['college'])->first();
        if (!$college) {
            $this->skipped[] = "College '{$row['college']}' does not exist for '{$row['first_name']} {$row['last_name']}'.";
            return null;
        }

        // Check if department exists within the specified college
        $department = Department::where('name', $row['department'])
                                ->where('college_id', $college->id)
                                ->first();
        if (!$department) {
            $this->skipped[] = "Department '{$row['department']}' does not exist in '{$row['college']}' for '{$row['first_name']} {$row['last_name']}'.";
            return null;
        }

        // Increment successful import count for new records
        $this->successfulImports++;

        // Set the role to "Instructor" by default
        $role = Role::where('name', 'instructor')->first();

        return new User([
            'rfid_number'     => $row['rfid_number'],
            'first_name'      => $row['first_name'],
            'middle_name'     => $row['middle_name'],
            'last_name'       => $row['last_name'],
            'suffix'          => $row['suffix'],
            'username'        => $row['username'],
            'email'           => $row['email'],
            'password'        => Hash::make('default_password'), // Set default password or specific one
            'status'          => $row['status'] ?? 'active',
            'role_id'         => optional($role)->id, // Assign "instructor" role by default
            'college_id'      => $college->id,
            'department_id'   => $department->id,
            'created_at'      => $row['created_at'] ?? now(),
            'updated_at'      => $row['updated_at'] ?? now(),
        ]);
    }

    public function rules(): array
    {
        return [
            'rfid_number' => 'required|string|max:255',
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'username'    => 'required|string|max:255',
            'email'       => 'required|email',
            'college'     => 'required|string|max:255',
            'department'  => 'required|string|max:255',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        $this->failures = $failures;
    }
}
