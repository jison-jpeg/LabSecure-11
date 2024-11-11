<?php

namespace App\Livewire;

use App\Exports\SubjectExport;
use App\Imports\SubjectImport;
use App\Models\Subject;
use App\Models\College;
use App\Models\Department;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class SubjectTable extends Component
{
    use WithPagination, WithFileUploads;
    protected $paginationTheme = 'bootstrap';

    public $subject;
    public $subjectFile;
    public $importErrors = []; // To hold any import errors


    public $title = 'Manage Subjects';
    public $event = 'create-subject';

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

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function clear()
    {
        $this->search = '';
        $this->college = '';
        $this->department = '';
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

    public function delete(Subject $subject)
    {
        $subject->delete();
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Subject deleted successfully');
    }

    public function importSubjects()
{
    $this->validate([
        'subjectFile' => 'required|file|mimes:csv,xlsx',
    ]);

    $import = new SubjectImport();

    try {
        Excel::import($import, $this->subjectFile->getRealPath());

        if (count($import->failures) > 0) {
            $this->importErrors = [];
            foreach ($import->failures as $failure) {
                $this->importErrors[] = "Row {$failure->row()}: {$failure->errors()[0]}";
            }
        }

        $successCount = $import->successfulImports;
        $skippedCount = count($import->skipped);

        // Prepare detailed success message for Notyf
        $message = "$successCount out of " . ($successCount + $skippedCount) . " subjects imported successfully.";
        if ($skippedCount > 0) {
            $message .= " $skippedCount subjects were skipped as they already exist.";
            $this->importErrors[] = "Skipped Subjects: ";
            foreach ($import->skipped as $skipped) {
                $this->importErrors[] = "{$skipped['name']} ({$skipped['code']})";
            }
        }

        // Display Notyf message
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success($message);

        // Close modal if no critical errors
        if (empty($this->importErrors)) {
            $this->dispatch('close-import-modal');
        }

    } catch (\Exception $e) {
        $this->importErrors = ['Error: ' . $e->getMessage()];
    }

    $this->reset('subjectFile');
}


    public function updatedSubjectFile()
    {
        $this->reset('importErrors');
    }



    public function exportAs($format)
    {
        switch ($format) {
            case 'csv':
                return Excel::download(new SubjectExport($this->search, $this->college, $this->department), 'subjects.csv', \Maatwebsite\Excel\Excel::CSV);
            case 'excel':
                return Excel::download(new SubjectExport($this->search, $this->college, $this->department), 'subjects.xlsx');
            case 'pdf':
                // Implement PDF export if needed
                break;
        }
    }

    public function render()
    {
        $user = Auth::user();
        $query = Subject::query();

        // Role-based filtering
        if ($user->isAdmin()) {
            // Admin sees all subjects
        } elseif ($user->isInstructor()) {
            // Instructors see subjects that are part of their assigned schedules
            $query->whereHas('schedules', function ($q) use ($user) {
                $q->where('instructor_id', $user->id);
            });
        } elseif ($user->isStudent()) {
            // Students see subjects that are part of their section schedules
            $query->whereHas('schedules', function ($q) use ($user) {
                $q->where('section_id', $user->section_id);
            });
        }

        // Apply search, filters, and sorting
        $subjects = $query->search($this->search)
            ->when($this->college !== '', function ($query) {
                $query->where('college_id', $this->college);
            })
            ->when($this->department !== '', function ($query) {
                $query->where('department_id', $this->department);
            })
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate($this->perPage);

        return view('livewire.subject-table', [
            'subjects' => $subjects,
            'colleges' => College::all(),
            'departments' => Department::all(),
        ]);
    }

    #[On('refresh-subject-table')]
    public function refreshSubjectTable()
    {
        $this->subject = Subject::all();
    }
}
