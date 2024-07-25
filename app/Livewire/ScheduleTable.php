<?php

namespace App\Livewire;

use App\Models\Schedule;
use Arr;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class ScheduleTable extends Component
{
    use WithPagination;

    public $schedule;

    public $title = 'Schedules';
    public $event = 'refresh-schedule-table';

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $sortBy = 'start_time';

    #[Url(history: true)]
    public $sortDir = 'ASC';

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
        $this->sortDir = 'ASC';
    }

    public function render()
{
    return view('livewire.schedule-table', [
        'schedules' => Schedule::search($this->search)
            ->with('subject', 'instructor', 'college', 'department', 'section', 'laboratory')
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate($this->perPage),
    ]);
}


    #[On('refresh-schedule-table')]
    public function refreshScheduleTable()
    {
        $this->schedule = Schedule::all();
    }
}
