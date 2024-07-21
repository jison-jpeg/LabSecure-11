<?php

namespace App\Livewire;

use App\Models\Laboratory;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class LaboratoryTable extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $type = '';

    #[Url(history: true)]
    public $sortBy = 'created_at';

    #[Url(history: true)]
    public $sortDir = 'DESC';

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
        $laboratory->delete();
    }

    public function render()
    {
        return view('livewire.laboratory-table', [
            'laboratories' => Laboratory::search($this->search)
            ->when($this->type !== '', function ($query){
                $query->where('type', $this->type);
            })
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate($this->perPage)
                
        ]);
    }
}
