<?php

namespace App\Livewire;

use App\Models\Laboratory;
use App\Models\TransactionLog;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LaboratoryTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $laboratory;
    public $title = 'Create Laboratory';
    public $event = 'create-laboratory';

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $type = '';

    #[Url(history: true)]
    public $sortBy = 'created_at';

    #[Url(history: true)]
    public $sortDir = 'ASC';

    #[Url(history: true)]
    public $perPage = 8;

    public $types = []; // Added property for laboratory types

    /**
     * Lifecycle hook: Called when the 'search' property is updated.
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }

    /**
     * Lifecycle hook: Called when the 'type' property is updated.
     * Resets the pagination to the first page.
     */
    public function updatedType()
    {
        $this->resetPage();
    }

    /**
     * Clears the search and type filters and resets pagination.
     */
    public function clear()
    {
        $this->search = '';
        $this->type = '';
        $this->resetPage();
    }

    /**
     * Sets the sorting field and direction.
     *
     * @param string $sortByField
     */
    public function setSortBy($sortByField)
    {
        if ($this->sortBy === $sortByField) {
            $this->sortDir = ($this->sortDir == 'ASC') ? 'DESC' : 'ASC';
            return;
        }

        $this->sortBy = $sortByField;
        $this->sortDir = 'DESC';
    }

    /**
     * Deletes a laboratory and logs the action.
     *
     * @param Laboratory $laboratory
     */
    public function delete(Laboratory $laboratory)
    {
        // Capture laboratory details before deletion for logging
        $laboratoryName = $laboratory->name;
        $laboratoryLocation = $laboratory->location;
        $userFullName = Auth::user()->full_name;

        // Delete the laboratory
        $laboratory->delete();

        // Log the delete action
        TransactionLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'model' => 'Laboratory',
            'model_id' => $laboratory->id,
            'details' => json_encode([
                'user' => $userFullName,
                'laboratory_name' => $laboratoryName,
                'location' => $laboratoryLocation,
                'action' => 'Deleted',
            ]),
        ]);

        // Dispatch refresh event and notify
        $this->dispatch('refresh-laboratory-table');
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Laboratory deleted successfully');
    }

    /**
     * Renders the Livewire component view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Fetch unique laboratory types with caching
        $this->types = Cache::remember('laboratory_types', 60, function () {
            return Laboratory::select('type')->distinct()->pluck('type');
        });

        // Fetch all laboratories with pagination and applied filters
        $laboratories = Laboratory::search($this->search)
            ->when($this->type !== '', function ($query) {
                $query->where('type', $this->type);
            })
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate($this->perPage);

        // Process each laboratory for recent logs
        foreach ($laboratories as $laboratory) {
            $recentLog = $laboratory->recentUserLog();

            if ($recentLog && $recentLog->user) {
                $laboratory->recent_user_name = $recentLog->user->full_name;
                $laboratory->recent_user_action = $recentLog->action == 'in' ? 'CURRENT USER' : 'RECENT USER';
                $laboratory->time_ago = $recentLog->created_at->diffForHumans();
            } else {
                $laboratory->recent_user_name = 'N/A';
                $laboratory->recent_user_action = 'N/A';
                $laboratory->time_ago = 'No Recent Activity';
            }
        }

        return view('livewire.laboratory-table', [
            'laboratories' => $laboratories,
        ]);
    }

    /**
     * Handles the 'refresh-laboratory-table' event.
     */
    #[On('refresh-laboratory-table')]
    public function refreshUserTable()
    {
        $this->laboratory = Laboratory::all();
    }
}
