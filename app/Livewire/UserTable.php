<?php

namespace App\Livewire;

use App\Exports\UsersExport;
use App\Models\College;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\TransactionLog;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class UserTable extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $user;
    public $exporting = false;
    public $title = 'Create User';
    public $event = 'create-user';
    public $selectedUsers = []; // Array to store selected user IDs
    public $selectAll = false; // Flag for "Select All"

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $role = '';

    #[Url(history: true)]
    public $sortBy = 'created_at';

    #[Url(history: true)]
    public $sortDir = 'DESC';

    #[Url()]
    public $perPage = 10;

    public function updatedSearch()
    {
        $this->resetPaage();
    }

    public function clear()
    {
        $this->search = '';
        $this->role = '';
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

    // Bulk delete selected users
    public function deleteSelected()
    {
        $users = User::whereIn('id', $this->selectedUsers)->get();

        foreach ($users as $user) {
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

        // Reset the selected users array and toggle off the "select all"
        $this->selectedUsers = [];
        $this->selectAll = false;

        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Selected users deleted successfully');

        $this->refreshUserTable();
    }

    // Method to toggle selecting all users across all pages
    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            // Select all matching users across all pages (not just current page)
            $this->selectedUsers = User::search($this->search)
                ->when($this->role !== '', function ($query) {
                    $query->where('role_id', $this->role);
                })
                ->pluck('id') // Get all matching user IDs
                ->toArray();
        } else {
            // Clear the selection
            $this->selectedUsers = [];
        }
    }

    public function exportAs($format)
    {
        switch ($format) {
            case 'csv':
                return Excel::download(new UsersExport($this->search, $this->role), 'users.csv', \Maatwebsite\Excel\Excel::CSV);
            case 'excel':
                return Excel::download(new UsersExport($this->search, $this->role), 'users.xlsx');
            case 'pdf':
                // Implement PDF export if needed
                break;
        }
    }

    public function render()
    {
        return view('livewire.user-table', [
            'users' => User::search($this->search)
                ->when($this->role !== '', function ($query) {
                    $query->where('role_id', $this->role);
                })
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate($this->perPage),
            'colleges' => College::all(),
            'departments' => Department::all(),
        ]);
    }

    #[On('refresh-user-table')]
    public function refreshUserTable()
    {
        $this->user = User::all();
    }
}

