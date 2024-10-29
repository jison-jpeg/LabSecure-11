<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\College;
use App\Models\Department;
use App\Models\Schedule;
use App\Models\Section;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class StudentTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

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

    // To store the filtered departments dynamically
    public $filteredDepartments = [];

    public function mount()
    {
        // Initialize the component with the authenticated user
        $this->user = Auth::user();

        // Initialize filteredDepartments based on user role
        $this->initializeFilteredDepartments();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCollege()
    {
        $this->resetPage();
        $this->department = ''; // Reset department when college changes
        $this->initializeFilteredDepartments();
    }

    public function clear()
    {
        $this->search = '';
        $this->college = '';
        $this->department = '';
        $this->scheduleCode = '';
        $this->section = '';
        $this->resetPage();
        $this->initializeFilteredDepartments();
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

    public function delete(User $student)
    {
        $student->delete();
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Student deleted successfully');
    }

    public function import()
    {
        // Implement import functionality
    }

    /**
     * Initialize filteredDepartments based on user role and selected college
     */
    private function initializeFilteredDepartments()
    {
        if ($this->user->isAdmin()) {
            if ($this->college !== '') {
                $this->filteredDepartments = Department::where('college_id', $this->college)->get();
            } else {
                $this->filteredDepartments = Department::all();
            }
        } elseif ($this->user->isDean()) {
            // For Dean, departments are within their college
            $this->filteredDepartments = Department::where('college_id', $this->user->college_id)->get();
        } else {
            // For Chairperson and other roles, no department filter is needed
            $this->filteredDepartments = collect();
        }
    }

    public function render()
    {
        $user = $this->user;

        // Initialize the query with only students
        $query = User::query()->with(['college', 'department', 'section'])
            ->whereHas('role', function ($q) {
                $q->where('name', 'student');
            });

        // Apply role-based filters
        if ($user->isAdmin()) {
            // Admin sees all students

            // Apply College filter if selected
            if ($this->college !== '') {
                $query->where('college_id', $this->college);
            }

            // Apply Department filter if selected
            if ($this->department !== '') {
                $query->where('department_id', $this->department);
            }
        } elseif ($user->isDean()) {
            // Dean sees all students in their college
            $query->where('college_id', $user->college_id);

            // Apply Department filter if selected
            if ($this->department !== '') {
                $query->where('department_id', $this->department);
            }
        } elseif ($user->isChairperson()) {
            // Chairperson sees students in their department only
            $query->where('department_id', $user->department_id);
        } elseif ($user->isInstructor()) {
            // Instructor sees only students in their schedules
            $query->whereHas('section.schedules', function ($q) use ($user) {
                $q->where('instructor_id', $user->id);
            });
        }

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('middle_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('suffix', 'like', '%' . $this->search . '%')
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

        $students = $query->orderBy($this->sortBy, $this->sortDir)
            ->paginate($this->perPage);

        // Determine which filters to show based on role
        $colleges = $user->isAdmin() ? College::all() : collect([]);
        $departments = ($user->isAdmin() || $user->isDean()) ? $this->filteredDepartments : collect([]);
        $schedules = $user->isInstructor() ? Schedule::where('instructor_id', $user->id)->get() : Schedule::all();
        $sections = Section::all();  // Fetch all sections regardless of the filters

        return view('livewire.student-table', [
            'users' => $students,
            'colleges' => $colleges,
            'departments' => $departments,
            'schedules' => $schedules,
            'sections' => $sections,
        ]);
    }
}
