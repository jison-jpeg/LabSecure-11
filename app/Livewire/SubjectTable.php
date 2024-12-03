<?php

namespace App\Livewire;

use App\Exports\SubjectExport;
use App\Imports\SubjectImport;
use App\Models\Subject;
use App\Models\College;
use App\Models\Department;
use Barryvdh\DomPDF\Facade\Pdf;
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
    public $importErrors = [];
    public $importSummary = '';
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

            $successCount = $import->successfulImports;
            $skippedCount = count($import->skipped);
            $totalCount = $successCount + $skippedCount;

            if (count($import->failures) > 0) {
                $this->importErrors = [];
                foreach ($import->failures as $failure) {
                    $this->importErrors[] = "Row {$failure->row()}: {$failure->errors()[0]}";
                }
                return;
            } elseif ($skippedCount > 0) {
                $this->importSummary = "$successCount out of $totalCount subjects imported successfully. $skippedCount subjects were skipped as they already exist.";
                $this->importErrors = [];
            } else {
                $message = "$totalCount subjects imported successfully.";
                notyf()
                    ->position('x', 'right')
                    ->position('y', 'top')
                    ->success($message);

                $this->dispatch('close-import-modal');
                $this->reset(['importErrors', 'importSummary']);
            }
        } catch (\Exception $e) {
            $this->importErrors = ['Error: ' . $e->getMessage()];
            $this->importSummary = '';

            notyf()
                ->position('x', 'right')
                ->position('y', 'top')
                ->error('An unexpected error occurred during import.');
        }

        $this->reset('subjectFile');
    }

    public function updatedSubjectFile()
    {
        $this->reset(['importErrors', 'importSummary']);
    }

    public function exportAs($format)
{
    $timestamp = now()->format('Y_m_d_H_i_s'); // Include date and time in filenames
    $fileName = "Subject_Export_{$timestamp}";

    switch ($format) {
        case 'csv':
            return Excel::download(new SubjectExport($this->search, $this->college, $this->department), "{$fileName}.csv");
        case 'excel':
            return Excel::download(new SubjectExport($this->search, $this->college, $this->department), "{$fileName}.xlsx");
        case 'pdf':
            // Group subjects by colleges and departments
            $colleges = College::with(['departments.subjects' => function ($query) {
                $query->when($this->college !== '', function ($query) {
                    $query->where('college_id', $this->college);
                })
                ->when($this->department !== '', function ($query) {
                    $query->where('department_id', $this->department);
                });
            }])
            ->when($this->college !== '', function ($query) {
                $query->where('id', $this->college);
            })
            ->get();

            // Generate the PDF view
            $pdf = Pdf::loadView('exports.subject_report', [
                'colleges' => $colleges,
                'collegeFilter' => $this->college ? College::find($this->college)->name : 'All',
                'departmentFilter' => $this->department ? Department::find($this->department)->name : 'All',
                'generatedBy' => Auth::user()->full_name,
            ])->setPaper('a4', 'portrait');

            // Stream the PDF download
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, "{$fileName}.pdf");

        default:
            notyf()->error('Unsupported export format.');
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
