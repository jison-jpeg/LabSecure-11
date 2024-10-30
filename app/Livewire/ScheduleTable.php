<?php

namespace App\Livewire;

use App\Exports\ScheduleExport;
use App\Models\Schedule;
use App\Models\College;
use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ScheduleTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $user;
    public $title = 'Manage Schedules';
    public $event = 'create-schedule';

    // Filter Properties
    public $search = '';
    public $sortBy = 'created_at';
    public $sortDir = 'DESC';
    public $perPage = 10;

    public $college = '';
    public $department = '';

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
        $this->sortBy = 'created_at';
        $this->sortDir = 'DESC';
        $this->perPage = 10;
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

    public function delete(Schedule $schedule)
    {
        $schedule->delete();
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Schedule deleted successfully');
    }

    public function exportAs($format)
    {
        switch ($format) {
            case 'csv':
                return Excel::download(new ScheduleExport($this->getExportFilters()), 'schedules.csv', \Maatwebsite\Excel\Excel::CSV);
            case 'excel':
                return Excel::download(new ScheduleExport($this->getExportFilters()), 'schedules.xlsx');
            case 'pdf':
                // Implement PDF export if needed
                // Example:
                // return Excel::download(new ScheduleExport($this->getExportFilters()), 'schedules.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
                break;
            default:
                // Handle unsupported formats if necessary
                break;
        }
    }

    /**
     * Get export filters based on current filters
     */
    private function getExportFilters()
    {
        return [
            'search' => $this->search,
            'college' => $this->college,
            'department' => $this->department,
            'sortBy' => $this->sortBy,
            'sortDir' => $this->sortDir,
        ];
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

        // Initialize the query
        $query = Schedule::query()->with(['college', 'department', 'section', 'instructor']);

        // Apply role-based filters
        if ($user->isAdmin()) {
            // Admin sees all schedules

            // Apply College filter if selected
            if ($this->college !== '') {
                $query->where('college_id', $this->college);
            }

            // Apply Department filter if selected
            if ($this->department !== '') {
                $query->where('department_id', $this->department);
            }
        } elseif ($user->isDean()) {
            // Dean sees all schedules within their college
            $query->where('college_id', $user->college_id);

            // Apply Department filter if selected
            if ($this->department !== '') {
                $query->where('department_id', $this->department);
            }
        } elseif ($user->isChairperson()) {
            // Chairperson sees all schedules within their department only
            $query->where('department_id', $user->department_id);
        } elseif ($user->isInstructor()) {
            // Instructor sees only schedules assigned to them
            $query->where('instructor_id', $user->id);
        } elseif ($user->isStudent()) {
            // Student sees only their own schedules
            // Assuming schedules are associated with sections and students are assigned to sections
            // Therefore, fetch schedules based on the student's section
            if ($user->section_id) {
                $query->where('section_id', $user->section_id);
            } else {
                // If the student is not assigned to any section, return no schedules
                $query->whereNull('id'); // This ensures no results are returned
            }
        }

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('schedule_code', 'like', '%' . $this->search . '%')
                  // Removed the 'title' field to prevent SQL errors
                  ->orWhereHas('instructor', function ($q2) {
                      $q2->where('first_name', 'like', '%' . $this->search . '%')
                         ->orWhere('last_name', 'like', '%' . $this->search . '%');
                  });
                // Add more searchable fields as needed
            });
        }

        $schedules = $query->sort($this->sortBy, $this->sortDir)
                           ->paginate($this->perPage);

        // Determine which filters to show based on role
        $colleges = $user->isAdmin() ? College::all() : collect([]);
        $departments = ($user->isAdmin() || $user->isDean()) ? $this->filteredDepartments : collect([]);
        // Schedules for export can include all or be filtered as needed
        // Sections can be filtered similarly if needed

        return view('livewire.schedule-table', [
            'schedules' => $schedules,
            'colleges' => $colleges,
            'departments' => $departments,
        ]);
    }
}
