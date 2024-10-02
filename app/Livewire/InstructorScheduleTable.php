<?php

namespace App\Livewire;

use App\Models\Schedule;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class InstructorScheduleTable extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $schedule;
    public $title = 'My Schedule';
    public $event = 'refresh-instructor-schedule-table';

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

        return view('livewire.instructor-schedule-table', [
            'schedules' => Schedule::where('instructor_id', $user->id)
                ->search($this->search)
                ->sort($this->sortBy, $this->sortDir)
                ->paginate($this->perPage),
        ]);
    }

    #[On('refresh-instructor-schedule-table')]
    public function refreshInstructorScheduleTable()
    {
        $this->schedule = Schedule::where('instructor_id', Auth::id())->get();
    }
}
