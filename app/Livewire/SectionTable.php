<?php

namespace App\Livewire;

use App\Exports\SectionExport;
use App\Models\Section;
use App\Models\College;
use App\Models\Department;
use App\Imports\SectionImport;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class SectionTable extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    public $title = 'Manage Sections';
    public $event = 'create-section';
    public $sectionFile;
    public $importErrors = [];
    public $importSummary = '';

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $college = '';

    #[Url(history: true)]
    public $department = '';

    #[Url(history: true)]
    public $sortBy = 'name';

    #[Url(history: true)]
    public $sortDir = 'ASC';

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

    public function importSections()
    {
        $this->validate([
            'sectionFile' => 'required|file|mimes:csv,xlsx',
        ]);

        $import = new SectionImport();

        try {
            Excel::import($import, $this->sectionFile->getRealPath());

            // Track success and skipped counts
            $successCount = $import->successfulImports;
            $skippedCount = count($import->skipped);
            $totalCount = $successCount + $skippedCount;

            if (count($import->failures) > 0) {
                // Collect row-level validation errors to display in modal
                $this->importErrors = [];
                foreach ($import->failures as $failure) {
                    $this->importErrors[] = "Row {$failure->row()}: " . implode(", ", $failure->errors());
                }
                return;
            } elseif ($skippedCount > 0) {
                // Partial success: Display summary with skipped details in modal
                $skippedDetails = implode(", ", $import->skipped);
                $this->importSummary = "$successCount out of $totalCount sections imported successfully. $skippedCount sections were skipped: $skippedDetails.";
                $this->importErrors = [];
            } else {
                // Full success: Show success message in Notyf if all records imported
                $message = "$totalCount sections imported successfully.";
                notyf()
                    ->position('x', 'right')
                    ->position('y', 'top')
                    ->success($message);

                // Close modal and reset fields
                $this->dispatch('close-import-modal');
                $this->reset(['importErrors', 'importSummary']);
            }
        } catch (\Exception $e) {
            // Handle unexpected errors
            $this->importErrors = ['Error: ' . $e->getMessage()];
            $this->importSummary = '';

            notyf()
                ->position('x', 'right')
                ->position('y', 'top')
                ->error('An unexpected error occurred during import.');
        }

        $this->reset('sectionFile');
    }

    public function exportAs($format)
    {
        $timestamp = now()->format('Y_m_d_H_i_s');
        $fileName = "Section_Export_{$timestamp}";

        // Retrieve filtered colleges, departments, sections, and students
        $colleges = College::with(['departments.sections.students' => function ($query) {
            $query->when($this->department, function ($query) {
                $query->where('department_id', $this->department);
            });
        }])
            ->when($this->college, function ($query) {
                $query->where('id', $this->college);
            })
            ->get();

        switch ($format) {
            case 'csv':
                return Excel::download(new SectionExport($this->search, $this->college, $this->department), "{$fileName}.csv");
            case 'excel':
                return Excel::download(new SectionExport($this->search, $this->college, $this->department), "{$fileName}.xlsx");
            case 'pdf':
                $pdf = Pdf::loadView('exports.section_report', [
                    'colleges' => $colleges,
                    'generatedBy' => Auth::user()->full_name,
                ])->setPaper('a4', 'portrait');

                return response()->streamDownload(function () use ($pdf) {
                    echo $pdf->output();
                }, "{$fileName}.pdf");

            default:
                notyf()->error('Unsupported export format.');
                break;
        }
    }


    public function updatedSectionFile()
    {
        $this->reset(['importErrors', 'importSummary']);
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
