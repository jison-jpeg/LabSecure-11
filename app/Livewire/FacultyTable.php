<?php

namespace App\Livewire;

use App\Exports\FacultyExport;
use App\Models\College;
use App\Models\Department;
use App\Models\User;
use App\Imports\FacultyImport;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class FacultyTable extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    public $faculty;
    public $title = 'Create Faculty';
    public $event = 'create-faculty';
    public $facultyFile;
    public $importErrors = [];
    public $importSummary = '';

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $sortBy = 'created_at';

    #[Url(history: true)]
    public $sortDir = 'DESC';

    #[Url()]
    public $perPage = 10;

    #[Url(history: true)]
    public $college = '';

    #[Url(history: true)]
    public $department = '';

    // To store the filtered departments
    public $filteredDepartments = [];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCollege()
    {
        $this->resetPage();
        $this->department = ''; // Reset department when college changes
    }

    public function clear()
    {
        $this->search = '';
        $this->college = '';
        $this->department = '';
        $this->resetPage();
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

    public function delete(User $faculty)
    {
        $faculty->delete();
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Faculty deleted successfully');
    }

    public function importFaculties()
    {
        $this->validate([
            'facultyFile' => 'required|file|mimes:csv,xlsx',
        ]);

        $import = new FacultyImport();

        try {
            Excel::import($import, $this->facultyFile->getRealPath());

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
                $this->importSummary = "$successCount out of $totalCount faculties imported successfully. $skippedCount faculties were skipped: $skippedDetails.";
                $this->importErrors = [];
            } else {
                // Full success: Show success message in Notyf if all records imported
                $message = "$totalCount faculties imported successfully.";
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

        $this->reset('facultyFile');
    }

    public function exportAs($format)
    {
        $timestamp = now()->format('Y_m_d_H_i_s'); // Include date and time in filenames
        $fileName = "Faculty_Export_{$timestamp}";

        switch ($format) {
            case 'csv':
                return Excel::download(new FacultyExport($this->college, $this->department), "{$fileName}.csv");

            case 'excel':
                return Excel::download(new FacultyExport($this->college, $this->department), "{$fileName}.xlsx");

            case 'pdf':
                $faculties = User::whereHas('role', function ($query) {
                    $query->where('name', 'instructor'); // Use role name instead of role_id
                })
                    ->when($this->college, function ($query) {
                        $query->where('college_id', $this->college);
                    })
                    ->when($this->department, function ($query) {
                        $query->where('department_id', $this->department);
                    })
                    ->with(['college', 'department', 'role'])
                    ->get();

                $pdf = Pdf::loadView('exports.faculty_report', [
                    'faculties' => $faculties,
                    'collegeFilter' => College::find($this->college),
                    'departmentFilter' => Department::find($this->department),
                    'generatedBy' => Auth::user(),
                ])->setPaper('a4', 'portrait');

                return response()->streamDownload(function () use ($pdf) {
                    echo $pdf->output();
                }, "{$fileName}.pdf");

            default:
                notyf()->error('Unsupported export format.');
                break;
        }
    }

    public function updatedFacultyFile()
    {
        $this->reset(['importErrors', 'importSummary']);
    }

    public function render()
    {
        $user = Auth::user();

        // Initialize the query
        $query = User::whereHas('role', function ($q) {
            $q->where('name', 'instructor'); // Use the role name "instructor"
        })->search($this->search);

        // Apply role-based filters
        if ($user->isAdmin()) {
            // Admin can filter by college and department
            if ($this->college !== '') {
                $query->where('college_id', $this->college);
            }

            if ($this->department !== '') {
                $query->where('department_id', $this->department);
            }

            // Fetch departments based on selected college
            if ($this->college !== '') {
                $this->filteredDepartments = Department::where('college_id', $this->college)->get();
            } else {
                $this->filteredDepartments = Department::all();
            }
        } elseif ($user->isDean()) {
            // Dean can only see faculties from their college
            $query->where('college_id', $user->college_id);

            // Allow filtering by department within their college
            if ($this->department !== '') {
                $query->where('department_id', $this->department);
            }

            // Fetch departments within the dean's college
            $this->filteredDepartments = Department::where('college_id', $user->college_id)->get();
        } elseif ($user->isChairperson()) {
            // Chairperson can only see faculties from their department
            $query->where('department_id', $user->department_id);
            $this->filteredDepartments = collect(); // No department filter needed
        } else {
            // For other roles, default to no departments
            $this->filteredDepartments = collect();
        }

        $faculties = $query->orderBy($this->sortBy, $this->sortDir)
            ->paginate($this->perPage);

        // Determine which filters to show based on role
        $colleges = $user->isAdmin() ? College::all() : collect([]);
        // Departments are already handled above in $filteredDepartments

        return view('livewire.faculty-table', [
            'faculties' => $faculties,
            'colleges' => $colleges,
            'departments' => $this->filteredDepartments,
        ]);
    }


    #[On('refresh-faculty-table')]
    public function refreshFacultyTable()
    {
        $this->faculty = User::all();
    }
}
