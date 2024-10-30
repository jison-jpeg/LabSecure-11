<?php

namespace App\Livewire;

use App\Models\Section;
use App\Models\College;
use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class SectionTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $title = 'Manage Sections';
    public $event = 'create-section';

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $college = '';

    #[Url(history: true)]
    public $department = '';

    #[Url(history: true)]
    public $sortBy = 'created_at';

    #[Url(history: true)]
    public $sortDir = 'DESC';

    #[Url()]
    public $perPage = 10;

    // To store the available departments based on selected college
    public $availableDepartments = [];

    public function mount()
    {
        // Initialize filteredDepartments based on user role
        $this->initializeFilteredDepartments();

        // Initialize availableDepartments based on current filters and role
        $this->updateAvailableDepartments();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCollege()
    {
        $this->resetPage();
        $this->department = ''; // Reset Department when College changes
        $this->updateAvailableDepartments();
    }

    public function updatedDepartment()
    {
        $this->resetPage();
        // If Department changes, no need to reset any other filter
        // Additional logic can be added here if needed
    }

    public function clear()
    {
        $this->search = '';
        $this->college = '';
        $this->department = '';
        $this->resetPage();
        $this->initializeFilteredDepartments();
        $this->updateAvailableDepartments();
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

    public function delete(Section $section)
    {
        $section->delete();
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Section deleted successfully');
    }

    public function import()
    {
        // Implement import functionality
    }

    /**
     * Initialize availableDepartments based on user role and selected college
     */
    private function initializeFilteredDepartments()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            // Admin can filter by any College and Department
            $this->availableDepartments = $this->college 
                ? Department::where('college_id', $this->college)->get() 
                : Department::all();
        } elseif ($user->isDean()) {
            // Dean sees Departments within their College
            $this->availableDepartments = Department::where('college_id', $user->college_id)->get();
        } else {
            // Chairperson has no Department filter; set to empty collection
            $this->availableDepartments = collect();
        }
    }

    /**
     * Update availableDepartments when College filter changes (Admin only)
     */
    private function updateAvailableDepartments()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            if ($this->college !== '') {
                // Filter Departments by selected College
                $this->availableDepartments = Department::where('college_id', $this->college)->get();
            } else {
                // No College selected; show all Departments
                $this->availableDepartments = Department::all();
            }
        } elseif ($user->isDean()) {
            // Dean's Departments are already initialized in mount()
            // No further action needed unless College can change for Dean (unlikely)
            // Assuming Dean's College is fixed
            $this->availableDepartments = Department::where('college_id', $user->college_id)->get();
        } else {
            // Chairperson has no Department filter; set to empty collection
            $this->availableDepartments = collect();
        }
    }

    /**
     * Handle the 'refresh-section-table' event
     */
    #[On('refresh-section-table')]
    public function refreshSectionTable()
    {
        // Logic to refresh the section table if needed
        // For example, re-fetching data or resetting filters
        $this->resetPage();
        $this->initializeFilteredDepartments();
        $this->updateAvailableDepartments();
    }

    public function render()
    {
        $user = Auth::user();

        // Initialize the query with Sections
        $query = Section::with(['college', 'department'])
            ->search($this->search);

        // Apply role-based filters
        if ($user->isAdmin()) {
            // Admin can filter by College and Department
            if ($this->college !== '') {
                $query->where('college_id', $this->college);
            }

            if ($this->department !== '') {
                $query->where('department_id', $this->department);
            }
        } elseif ($user->isDean()) {
            // Dean sees Sections within their College
            $query->where('college_id', $user->college_id);

            // If a Department is selected, filter by department_id
            if ($this->department !== '') {
                $query->where('department_id', $this->department);
            }
        } elseif ($user->isChairperson()) {
            // Chairperson sees Sections within their Department only
            $query->where('department_id', $user->department_id);
        }

        // Apply sorting and pagination
        $sections = $query->orderBy($this->sortBy, $this->sortDir)
                          ->paginate($this->perPage);

        // Determine available Colleges (Admin only)
        $colleges = $user->isAdmin() ? College::all() : collect();

        // Departments are already handled in availableDepartments
        $departments = ($user->isAdmin() || $user->isDean()) ? $this->availableDepartments : collect();

        return view('livewire.section-table', [
            'sections' => $sections,
            'colleges' => $colleges,
            'departments' => $departments,
        ]);
    }
}
