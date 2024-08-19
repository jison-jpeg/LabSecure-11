<?php

namespace App\Livewire;

use App\Models\Laboratory;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class LaboratoryTable extends Component
{
    use WithPagination;

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

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function clear()
    {
        $this->search = '';
        $this->type = '';        
    }

    public function setSortBy($sortByField)
    {
        if ($this->sortBy === $sortByField) {
            $this->sortDir = ($this->sortDir == 'ASC') ? 'DESC' : 'ASC';
            return;
        }

        $this->sortBy = $sortByField;
        $this->sortDir = 'DESC';
    }

    public function delete(Laboratory $laboratory)
    {
        $this->dispatch('refresh-laboratory-table');
        $laboratory->delete();
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Laboratory deleted successfully');
    }

    public function placeholder(){
        return <<<'HTML'
        <div class="placeholder-glow">
            <div class="placeholder col-12">

            </div>
            </div>
        HTML;
    }

    public function render()
    {
        return view('livewire.laboratory-table', [
            'laboratories' => Laboratory::search($this->search)
                ->when($this->type !== '', function ($query) {
                    $query->where('type', $this->type);
                })
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate($this->perPage)
        ]);
    }

    #[On('refresh-laboratory-table')]
    public function refreshUserTable()
    {
        $this->laboratory = Laboratory::all();
    }
}
