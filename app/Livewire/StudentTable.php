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

    public $title = 'Manage Students';
    public $event = 'create-student';

    // Filter Properties
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

    // To store available sections based on department selection
    public $availableSections = [];

    public function mount()
    {
        // Initialize filteredDepartments based on user role
        $this->initializeFilteredDepartments();

        // Initialize availableSections based on current filters and role
        $this->updateAvailableSections();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCollege()
    {
        $this->resetPage();
        $this->department = ''; // Reset department when college changes
        $this->updateAvailableSections(); // Update Sections based on new Department
    }

    public function updatedDepartment()
    {
        $this->resetPage();
        $this->section = ''; // Reset Section when Department changes
        $this->updateAvailableSections(); // Update Sections based on new Department
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
        $this->updateAvailableSections();
    }

    public function setSortBy($sortByField)
    {
        if ($this->sortBy === $sortByField) {
            $this->sortDir = ($this->sortDir === "ASC") ? 'DESC' : "ASC";
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
        $user = Auth::user();

        if ($user->isAdmin()) {
            if ($this->college !== '') {
                $this->filteredDepartments = Department::where('college_id', $this->college)->get();
            } else {
                $this->filteredDepartments = Department::all();
            }
        } elseif ($user->isDean()) {
            // For Dean, departments are within their college
            $this->filteredDepartments = Department::where('college_id', $user->college_id)->get();
        } else {
            // For Chairperson, Instructor, and other roles, no department filter is needed
            $this->filteredDepartments = collect();
        }
    }

    /**
     * Get the sections managed by the instructor
     */
    private function getInstructorSections()
    {
        $user = Auth::user();

        if ($user->isInstructor()) {
            return Section::whereHas('schedules', function ($q) use ($user) {
                $q->where('instructor_id', $user->id);
            })->pluck('id')->toArray();
        }

        return [];
    }

    /**
     * Update the availableSections based on the selected department and user role
     */
    private function updateAvailableSections()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            if ($this->department !== '') {
                // Admin: Filter Sections by selected Department
                $this->availableSections = Section::where('department_id', $this->department)->get();
            } else {
                // Admin: Show all Sections
                $this->availableSections = Section::all();
            }
        } elseif ($user->isDean()) {
            if ($this->department !== '') {
                // Dean: Filter Sections by selected Department within their College
                $this->availableSections = Section::where('department_id', $this->department)
                    ->whereHas('department', function ($q) use ($user) {
                        $q->where('college_id', $user->college_id);
                    })
                    ->get();
            } else {
                // Dean: Show all Sections within their College
                $this->availableSections = Section::whereHas('department', function ($q) use ($user) {
                    $q->where('college_id', $user->college_id);
                })->get();
            }
        } elseif ($user->isChairperson()) {
            if ($this->department !== '') {
                // Chairperson: Ensure selected Department matches their own
                if ($this->department == $user->department_id) {
                    $this->availableSections = Section::where('department_id', $this->department)->get();
                } else {
                    // If Department does not match, return empty collection
                    $this->availableSections = collect();
                }
            } else {
                // Chairperson: Show Sections within their Department
                $this->availableSections = Section::where('department_id', $user->department_id)->get();
            }
        } elseif ($user->isInstructor()) {
            if ($this->department !== '') {
                // Instructor: Show Sections they manage within the selected Department
                $instructorSections = $this->getInstructorSections();
                $this->availableSections = Section::whereIn('id', $instructorSections)
                    ->where('department_id', $this->department)
                    ->get();
            } else {
                // Instructor: Show all Sections they manage
                $instructorSections = $this->getInstructorSections();
                $this->availableSections = Section::whereIn('id', $instructorSections)->get();
            }
        } else {
            // For other roles or unauthenticated users, show no Sections
            $this->availableSections = collect();
        }
    }

    public function render()
    {
        $user = Auth::user();

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
            // Instructor sees only students in their managed sections
            $instructorSections = $this->getInstructorSections();
            $query->whereIn('section_id', $instructorSections);
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

        // Get paginated students
        $students = $query->orderBy($this->sortBy, $this->sortDir)
            ->paginate($this->perPage);

        // Determine which filters to show based on role
        $colleges = $user->isAdmin() ? College::all() : collect([]);
        $departments = ($user->isAdmin() || $user->isDean()) ? $this->filteredDepartments : collect([]);
        $schedules = ($user->isAdmin() || $user->isDean() || $user->isChairperson() || $user->isInstructor()) 
            ? ($user->isInstructor() ? Schedule::where('instructor_id', $user->id)->get() : Schedule::all()) 
            : collect([]);
        $sections = $this->availableSections; // Use the dynamically updated availableSections

        return view('livewire.student-table', [
            'users' => $students,
            'colleges' => $colleges,
            'departments' => $departments,
            'schedules' => $schedules,
            'sections' => $sections, // Pass availableSections as 'sections'
        ]);
    }
}
