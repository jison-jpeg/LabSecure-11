<?php

namespace App\Exports;

use App\Models\User;
use App\Models\College;
use App\Models\Department;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\FromQuery;

class FacultyExport implements FromQuery, WithHeadings, WithMapping
{
    protected $college;
    protected $department;

    public function __construct($college = null, $department = null)
    {
        $this->college = $college;
        $this->department = $department;
    }

    public function query()
    {
        return User::whereHas('role', function ($query) {
            $query->where('name', 'instructor'); // Use role name
        })
            ->when($this->college, function ($query) {
                $query->where('college_id', $this->college);
            })
            ->when($this->department, function ($query) {
                $query->where('department_id', $this->department);
            })
            ->with(['college', 'department', 'role']);
    }

    public function headings(): array
    {
        return ['#', 'Username', 'First Name', 'Last Name', 'Email', 'Role', 'College', 'Department', 'Status'];
    }

    public function map($faculty): array
    {
        return [
            $faculty->id,
            $faculty->username,
            $faculty->first_name,
            $faculty->last_name,
            $faculty->email,
            optional($faculty->role)->name,
            optional($faculty->college)->name,
            optional($faculty->department)->name,
            ucfirst($faculty->status),
        ];
    }
}
