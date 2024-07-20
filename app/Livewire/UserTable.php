<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class UserTable extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $role = '';

    #[Url(history:true)]
    public $sortBy = 'created_at';

    #[Url(history:true)]
    public $sortDir = 'DESC';

    #[Url()]
    public $perPage = 10;

    public function updatedSearch(){
        $this->resetPage();
    }

    public function clear()
    {
        $this->search = '';
        $this->role = '';        
    }

    public function setSortBy($sortByField)
    {
        if($this->sortBy === $sortByField) {
            $this->sortDir = ($this->sortDir == "ASC") ? 'DESC' : "ASC";
            return;
        }

        $this->sortBy = $sortByField;
        $this->sortDir = 'DESC';
    }

    public $first_name = '';
    public $middle_name = '';
    public $last_name = '';
    public $suffix = '';
    public $username = '';
    public $email = '';
    public $password = '';
    public $role_id = '';
    public $college_id = '';
    public $department_id = '';

    public function delete(User $user)
    {
        $user->delete();
        
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
        ]);
    }
}
