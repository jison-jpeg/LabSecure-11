<?php

namespace App\Livewire;

use App\Models\College;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\TransactionLog;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class UserTable extends Component
{
    use WithPagination;

    public $user;
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
