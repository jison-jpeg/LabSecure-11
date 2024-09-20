<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\TransactionLog;
use App\Models\Schedule;
use Livewire\Component;
use Livewire\WithPagination;

class ViewUser extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $user;
    public $perPage = 10;
    public $search = '';
    public $action = '';
    public $role = '';

    public function mount(User $user)
    {
        // Initialize the user
        $this->user = $user;
    }

    public function clear()
    {
        $this->search = '';
        $this->action = '';
        $this->role = '';
    }

    public function getLogsProperty()
    {
        $search = trim(preg_replace('/\s+/', ' ', $this->search));

        // Fetch logs for the specific user
        return TransactionLog::where('user_id', $this->user->id)
            ->when($search, function ($query) use ($search) {
                $query->where('action', 'like', '%' . $search . '%')
                      ->orWhere('model', 'like', '%' . $search . '%')
                      ->orWhere('details', 'like', '%' . $search . '%');
            })
            ->when($this->action, function ($query) {
                $query->where('action', $this->action);
            })
            ->latest()
            ->paginate($this->perPage);
    }

    public function getSchedulesProperty()
    {
        // Fetch user's schedules
        return $this->user->schedules()->with('subject', 'section', 'instructor', 'laboratory')->get();
    }

    public function render()
    {
        return view('livewire.view-user', [
            'user' => $this->user,
            'logs' => $this->logs,
            'schedules' => $this->schedules, // Pass schedules to the view
        ]);
    }
}
