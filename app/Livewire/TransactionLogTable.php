<?php

namespace App\Livewire;

use App\Models\TransactionLog;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class TransactionLogTable extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $action = '';

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
        $this->action = '';
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

    public function delete(TransactionLog $log)
    {
        $log->delete();
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Transaction log deleted successfully');
    }

    public function render()
    {
        return view('livewire.transaction-log-table', [
            'logs' => TransactionLog::with('user')
                ->search($this->search)
                ->when($this->action !== '', function ($query) {
                    $query->where('action', $this->action);
                })
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate($this->perPage),
        ]);
    }
}
