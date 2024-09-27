<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\College;
use App\Models\Department;
use App\Models\Schedule;
use App\Models\Section;
use Livewire\Component;
use Livewire\WithPagination;

class StudentTable extends Component
{
    use WithPagination;

    public $user;
    public $title = 'Create Student';
    public $event = 'create-student';

    public $search = '';
    public $sortBy = 'created_at';
    public $sortDir = 'DESC';
    public $perPage = 10;

    public $college = '';
    public $department = '';
    public $scheduleCode = '';
    public $section = '';

    public function mount()
    {
        // Initialize the component with the authenticated user
        $this->user = auth()->user();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function clear()
    {
        $this->search = '';
        $this->college = '';
        $this->department = '';
        $this->scheduleCode = '';
        $this->section = '';
    }

    public function setSortBy($sortByField)
    {
        if ($this->sortBy === $sortByField) {
            $this->sortDir = ($this->sortDir == "ASC") ? 'DESC' : "ASC";
            return;
        }

        $this->sortBy = $sortByField;
        $this->sortDir = 'DESC';
    }

    public function render()
    {
        $query = User::query()->with(['college', 'department', 'section']);

        // Apply role-based filters
        if ($this->user->isAdmin()) {
            // Admin sees all students
            $query->where('role_id', 3);
        } elseif ($this->user->isInstructor()) {
            // Instructor sees only students in their schedules
            $query->whereHas('section.schedules', function ($q) {
                $q->where('instructor_id', $this->user->id);
            });
        } elseif ($this->user->isDean()) {
            // Dean sees students in their college and department
            $query->where('college_id', $this->user->college_id)
                ->where('department_id', $this->user->department_id);
        } elseif ($this->user->isChairperson()) {
            // Chairperson sees students in their department
            $query->where('department_id', $this->user->department_id);
        }

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('username', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        // Apply additional filters based on user role
        if ($this->scheduleCode) {
            $query->whereHas('section.schedules', function ($q) {
                $q->where('schedule_code', $this->scheduleCode);
            });
        }

        if ($this->section) {
            $query->where('section_id', $this->section);
        }

        if ($this->college && $this->user->isAdmin()) {
            $query->where('college_id', $this->college);
        }

        if ($this->department && ($this->user->isAdmin() || $this->user->isDean())) {
            $query->where('department_id', $this->department);
        }

        $users = $query->orderBy($this->sortBy, $this->sortDir)->paginate($this->perPage);

        // Always fetch all available options for filtering
        $colleges = $this->user->isAdmin() ? College::all() : [];
        $departments = $this->user->isAdmin() || $this->user->isDean() ? Department::all() : [];
        $schedules = $this->user->isInstructor() ? Schedule::where('instructor_id', $this->user->id)->get() : Schedule::all();
        $sections = Section::all();  // Fetch all sections regardless of the filters

        return view('livewire.student-table', [
            'users' => $users,
            'colleges' => $colleges,
            'departments' => $departments,
            'schedules' => $schedules,
            'sections' => $sections,
        ]);
    }
}
