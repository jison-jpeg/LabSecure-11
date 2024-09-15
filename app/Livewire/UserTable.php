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

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $role = '';

    #[Url(history: true)]
    public $sortBy = 'created_at';

    #[Url(history: true)]
    public $sortDir = 'DESC';
    
    public $selected_user_id = []; // Array to store selected user IDs

    #[Url()]
    public $perPage = 10;

    public function updatedSearch()
    {
        $this->resetPage();
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

    // Bulk delete function
    public function deleteSelected()
    {
        // Fetch the selected users
        $usersToDelete = User::whereIn('id', $this->selected_user_id)->get();

        foreach ($usersToDelete as $user) {
            // Log each deletion in the TransactionLog
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

            // Delete the user
            $user->delete();
        }

        // Reset selected users array
        $this->selected_user_id = [];

        // Notify the user
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success(count($usersToDelete) . ' users deleted successfully.');

        // Refresh the user table after deletion
        $this->refreshUserTable();
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
