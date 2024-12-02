<?php

namespace App\Livewire;

use App\Exports\CollegeExport;
use App\Imports\CollegeImport;
use App\Models\College;
use App\Models\TransactionLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class CollegeTable extends Component
{
    use WithPagination, WithFileUploads;
    protected $paginationTheme = 'bootstrap';
    public $college;
    public $collegeFile;
    public $importErrors = [];
    public $importSummary = '';
    public $title = 'Create College';
    public $event = 'create-college';

    #[Url(history: true)]
    public $search = '';

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

    public function delete(College $college)
    {
        TransactionLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'model' => 'College',
            'model_id' => $college->id,
            'details' => json_encode([
                'college_name' => $college->name,
                'user' => Auth::user()->full_name,
                'username' => Auth::user()->username,
            ]),
        ]);
        $college->delete();
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('College deleted successfully');
    }

    public function importColleges()
{
    $this->validate([
        'collegeFile' => 'required|file|mimes:csv,xlsx',
    ]);

    $import = new CollegeImport();

    try {
        Excel::import($import, $this->collegeFile->getRealPath());

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
            $this->importSummary = "$successCount out of $totalCount colleges imported successfully. $skippedCount colleges were skipped: $skippedDetails.";
            $this->importErrors = [];

        } else {
            // Full success: Show success message in Notyf if all records imported
            $message = "$totalCount colleges imported successfully.";
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

    $this->reset('collegeFile');
}

    public function updatedCollegeFile()
    {
        $this->reset(['importErrors', 'importSummary']);
    }

    public function exportAs($format)
{
    $timestamp = now()->format('Y_m_d_H_i_s'); // Current date and time
    $fileName = "College_and_Departments_{$timestamp}";

    switch ($format) {
        case 'csv':
            return Excel::download(new CollegeExport(), "{$fileName}.csv", \Maatwebsite\Excel\Excel::CSV);
        case 'excel':
            return Excel::download(new CollegeExport(), "{$fileName}.xlsx");
        case 'pdf':
            $colleges = College::with(['departments.users'])->get();

            $pdf = Pdf::loadView('exports.college_report', [
                'colleges' => $colleges,
            ])->setPaper('a4', 'portrait')
              ->setOption('margin-top', '10mm')
              ->setOption('margin-bottom', '10mm')
              ->setOption('margin-left', '10mm')
              ->setOption('margin-right', '10mm');

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, "{$fileName}.pdf");

        default:
            notyf()->position('x', 'right')->position('y', 'top')->error('Unsupported export format.');
            break;
    }
}

    public function render()
    {
        return view('livewire.college-table', [
            'colleges' => College::search($this->search)
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate($this->perPage),
        ]);
    }

    #[On('refresh-college-table')]
    public function refreshCollegeTable()
    {
        $this->college = College::all();
    }
}
