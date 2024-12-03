<?php

namespace App\Exports;

use App\Models\User;
use App\Models\College;
use App\Models\Department;
use App\Models\Section;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\FromQuery;

class StudentExport implements FromQuery, WithHeadings, WithMapping
{
    protected $college;
    protected $department;
    protected $yearLevel;
    protected $section;

    public function __construct($college = null, $department = null, $yearLevel = null, $section = null)
    {
        $this->college = $college;
        $this->department = $department;
        $this->yearLevel = $yearLevel;
        $this->section = $section;
    }

    public function query()
    {
        return User::whereHas('role', function ($query) {
            $query->where('name', 'student'); // Use role name for students
        })
        ->when($this->college, function ($query) {
            $query->where('college_id', $this->college);
        })
        ->when($this->department, function ($query) {
            $query->where('department_id', $this->department);
        })
        ->when($this->yearLevel, function ($query) {
            $query->whereHas('section', function ($q) {
                $q->where('year_level', $this->yearLevel);
            });
        })
        ->when($this->section, function ($query) {
            $query->where('section_id', $this->section);
        })
        ->with(['college', 'department', 'section', 'role']);
    }

    public function headings(): array
    {
        return ['#', 'Username', 'First Name', 'Last Name', 'Email', 'Role', 'College', 'Department', 'Section', 'Year Level', 'Status'];
    }

    public function map($student): array
    {
        return [
            $student->id,
            $student->username,
            $student->first_name,
            $student->last_name,
            $student->email,
            optional($student->role)->name,
            optional($student->college)->name,
            optional($student->department)->name,
            optional($student->section)->name,
            optional($student->section)->year_level,
            ucfirst($student->status),
        ];
    }
}
