<?php

namespace App\Livewire;

use App\Exports\UsersExport;
use App\Imports\UserImport;
use App\Models\College;
use App\Models\Department;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\TransactionLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class UserTable extends Component
{
    use WithPagination, WithFileUploads;
    protected $paginationTheme = 'bootstrap';

    public $user;
    public $userFile;
    public $importErrors = [];
    public $importSummary = '';
    public $exporting = false;
    public $title = 'Create User';
    public $event = 'create-user';

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $role = '';

    #[Url(history: true)]
    public $status = ''; // New property for the status filter

    #[Url(history: true)]
    public $sortBy = 'updated_at';

    #[Url(history: true)]
    public $sortDir = 'DESC';

    public $selected_user_id = []; // Array to store selected user IDs

    #[Url()]
    public $perPage = 10;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedRole()
    {
        $this->resetPage();
    }

    public function updatedStatus() // Reset pagination when status is updated
    {
        $this->resetPage();
    }

    public function clear()
    {
        $this->search = '';
        $this->role = '';
        $this->status = ''; // Reset the status filter
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

    public function delete(User $user)
    {
        TransactionLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'model' => 'User',
            'model_id' => $user->id,
            'details' => json_encode([
                'user' => $user->full_name,
                'username' => $user->username,
            ]),
        ]);

        $user->delete();
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('User deleted successfully');
    }

    public function deleteSelected()
    {
        $usersToDelete = User::whereIn('id', $this->selected_user_id)->get();

        foreach ($usersToDelete as $user) {
            TransactionLog::create([
                'user_id' => Auth::id(),
                'action' => 'delete',
                'model' => 'User',
                'model_id' => $user->id,
                'details' => json_encode([
                    'user' => $user->full_name,
                    'username' => $user->username,
                ]),
            ]);

            $user->delete();
        }

        $this->selected_user_id = [];

        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success(count($usersToDelete) . ' users deleted successfully.');

        $this->refreshUserTable();
    }

    public function importUsers()
    {
        $this->validate([
            'userFile' => 'required|file|mimes:csv,xlsx',
        ]);

        $import = new UserImport();

        try {
            Excel::import($import, $this->userFile->getRealPath());

            $successCount = $import->successfulImports;
            $skippedCount = count($import->skipped);
            $totalCount = $successCount + $skippedCount;

            if (count($import->failures) > 0) {
                $this->importErrors = [];
                foreach ($import->failures as $failure) {
                    $this->importErrors[] = "Row {$failure->row()}: " . implode(", ", $failure->errors());
                }
                return;
            } elseif ($skippedCount > 0) {
                $this->importSummary = "$successCount out of $totalCount users imported successfully. $skippedCount users were skipped as they already exist.";
                $this->importErrors = [];
            } else {
                notyf()
                    ->position('x', 'right')
                    ->position('y', 'top')
                    ->success("$totalCount users imported successfully.");

                $this->dispatch('close-import-modal');
                $this->reset(['importErrors', 'importSummary']);
            }
        } catch (\Exception $e) {
            $this->importErrors = ['Error: ' . $e->getMessage()];
            notyf()
                ->position('x', 'right')
                ->position('y', 'top')
                ->error('An unexpected error occurred during import.');
        }

        $this->reset('userFile');
    }

    public function updatedUserFile()
    {
        $this->reset(['importErrors', 'importSummary']);
    }

    public function exportAs($format)
    {
        $user = Auth::user(); // Get the authenticated user

        // Instantiate the UsersExport with role and status filters
        $export = new UsersExport($this->role, $this->status);

        switch ($format) {
            case 'csv':
                return Excel::download($export, 'users_' . now()->format('Y_m_d_H_i_s') . '.csv', \Maatwebsite\Excel\Excel::CSV);
            case 'excel':
                return Excel::download($export, 'users_' . now()->format('Y_m_d_H_i_s') . '.xlsx');
            case 'pdf':
                // Execute the query to get the data
                $users = $export->query()->get();

                // Load PDF view
                $pdf = Pdf::loadView('exports.user_report', [
                    'user' => $user,
                    'role' => $this->role,
                    'status' => $this->status,
                    'users' => $users,
                ])->setPaper('a4', 'portrait') // Use portrait orientation
                    ->setOption('margin-top', '10mm') // Adjust top margin
                    ->setOption('margin-bottom', '10mm') // Adjust bottom margin
                    ->setOption('margin-left', '10mm') // Adjust left margin
                    ->setOption('margin-right', '10mm'); // Adjust right margin

                // Stream the PDF for download
                return response()->streamDownload(function () use ($pdf) {
                    echo $pdf->output();
                }, 'user_report_' . now()->format('Y_m_d_H_i_s') . '.pdf');

            default:
                notyf()
                    ->position('x', 'right')
                    ->position('y', 'top')
                    ->error('Unsupported export format.');
                break;
        }
    }

    public function render()
    {
        return view('livewire.user-table', [
            'users' => User::query()
                ->when($this->search, function ($query) {
                    $query->where('username', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                })
                ->when($this->role, function ($query) {
                    $query->whereHas('role', function ($q) {
                        $q->where('name', $this->role);
                    });
                })
                ->when($this->status, function ($query) {
                    $query->where('status', $this->status);
                })
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate($this->perPage),
            'colleges' => College::all(),
            'departments' => Department::all(),
            'roles' => Role::all(),
        ]);
    }

    #[On('refresh-user-table')]
    public function refreshUserTable()
    {
        $this->user = User::all();
    }
}
