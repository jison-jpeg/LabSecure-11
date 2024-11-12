<?php

namespace App\Livewire;

use App\Exports\CollegeExport;
use App\Imports\DepartmentImport;
use App\Models\Department;
use App\Models\College;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class DepartmentTable extends Component
{
    use WithPagination, WithFileUploads;
    protected $paginationTheme = 'bootstrap';

    public $department;
    public $departmentFile;
    public $importErrors = [];
    public $importSummary = '';
    
    public $title = 'Create Department';
    public $event = 'create-department';

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $college = '';

    #[Url(history: true)]
    public $sortBy = 'created_at';

    #[Url(history: true)]
    public $sortDir = 'DESC';

    #[Url()]
    public $perPage = 10;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function clear()
    {
        $this->search = '';
        $this->college = '';
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

    public function delete(Department $department)
    {
        $department->delete();
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Department deleted successfully');
    }
    public function exportAs($format)
    {
        switch ($format) {
            case 'csv':
                return Excel::download(new CollegeExport(), 'College and Departments.csv', \Maatwebsite\Excel\Excel::CSV);
            case 'excel':
                return Excel::download(new CollegeExport(), 'College and Departments.xlsx');
            case 'pdf':
                // You can implement a PDF export logic if needed.
                break;
        }
    }

    public function importDepartments()
{
    $this->validate([
        'departmentFile' => 'required|file|mimes:csv,xlsx',
    ]);

    $import = new DepartmentImport();

    try {
        Excel::import($import, $this->departmentFile->getRealPath());

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
            $this->importSummary = "$successCount out of $totalCount departments imported successfully. $skippedCount departments were skipped: $skippedDetails.";
            $this->importErrors = [];

        } else {
            // Full success: Show success message in Notyf if all records imported
            $message = "$totalCount departments imported successfully.";
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

    $this->reset('departmentFile');
}

    public function updatedDepartmentFile()
    {
        $this->reset(['importErrors', 'importSummary']);
    }

    public function render()
    {
        return view('livewire.department-table', [
            'departments' => Department::with('college')
                ->search($this->search)
                ->when($this->college !== '', function ($query) {
                    $query->where('college_id', $this->college);
                })
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate($this->perPage),
            'colleges' => College::all(),
        ]);
    }

    #[On('refresh-department-table')]
    public function refreshDepartmentTable()
    {
        $this->department = Department::all();
    }
}
