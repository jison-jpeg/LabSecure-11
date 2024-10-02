<?php

namespace App\Livewire;

use App\Models\Schedule;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class StudentScheduleTable extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $schedule;
    public $title = 'My Schedule';
    public $event = 'refresh-student-schedule-table';

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
        $user = Auth::user();

        return view('livewire.student-schedule-table', [
            'schedules' => Schedule::where('section_id', $user->section_id)
                ->search($this->search)
                ->sort($this->sortBy, $this->sortDir)
                ->paginate($this->perPage),
        ]);
    }

    #[On('refresh-student-schedule-table')]
    public function refreshStudentScheduleTable()
    {
        $this->schedule = Schedule::where('section_id', Auth::user()->section_id)->get();
    }
}
