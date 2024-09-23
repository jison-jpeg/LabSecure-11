<?php

namespace App\Livewire;

use App\Models\Department;
use Livewire\Component;

class ViewDepartment extends Component
{
    public $department;

    public function mount(Department $department)
    {
        // Load the department with its related college to access the college name
        $this->department = $department->load('college');
    }

    public function render()
    {
        return view('livewire.view-department', [
            'department' => $this->department,
            'sections' => $this->department->sections,
            'subjects' => $this->department->subjects,
            'totalStudents' => $this->department->users()->whereHas('role', function ($q) {
                $q->where('name', 'student');
            })->count(),
            'totalInstructors' => $this->department->users()->whereHas('role', function ($q) {
                $q->where('name', 'instructor');
            })->count(),
        ]);
    }
}
