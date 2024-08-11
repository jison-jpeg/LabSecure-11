<?php

namespace App\Livewire;

use App\Models\College;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class CollegeTable extends Component
{
    use WithPagination;

    public $college;
    public $title = 'Create College';
    public $event = 'create-college';

    #[Url(history: true)]
    public $search = '';

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

    public function delete(College $college)
    {
        $college->delete();
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('College deleted successfully');
    }

    public function render()
    {
        return view('livewire.college-table', [
            'colleges' => College::search($this->search)
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate($this->perPage),
        ]);
    }

    #[On('refresh-college-table')]
    public function refreshCollegeTable()
    {
        $this->college = College::all();
    }
}