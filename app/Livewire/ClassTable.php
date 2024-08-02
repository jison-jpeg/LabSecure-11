<?php

namespace App\Livewire;

use App\Models\Section;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class ClassTable extends Component
{
    use WithPagination;

    public $title = 'Class Table';
    public $event = 'class-table';

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

    public function render()
    {
        $instructorId = Auth::id();
        
        return view('livewire.class-table', [
            'sections' => Section::whereHas('schedules', function($query) use ($instructorId) {
                    $query->where('instructor_id', $instructorId);
                })
                ->search($this->search)
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate($this->perPage),
        ]);
    }

    #[On('refresh-class-table')]
    public function refreshClassTable()
    {
        $this->sections = Section::all();
    }
}
