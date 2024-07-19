<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Url;

class UserTable extends Component
{
    
    // #[Url(history: true)]
    public $search = '';

    // #[Url(history: true)]
    public $role = '';

    #[Url()]
    public $perPage = 10;

    // public function updatedSearch()
    // {
    //     $this->resetPage();
    // }

    public function render()
    {
        return view('livewire.user-table',
            [
                'users' => User::search($this->search)
                    ->when($this->role !== '', function ($query) {
                        $query->where('role_id', $this->role);
                    })
                    ->paginate($this->perPage),
            ]
        );
    }
}
