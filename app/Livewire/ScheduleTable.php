<?php

namespace App\Livewire;

use App\Exports\ScheduleExport;
use App\Models\Schedule;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class ScheduleTable extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $schedule;
    public $title = 'Manage Schedules';
    public $event = 'create-schedule';

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

    public function delete(Schedule $schedule)
    {
        $schedule->delete();
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Schedule deleted successfully');
    }

    public function exportAs($format)
    {
        switch ($format) {
            case 'csv':
                return Excel::download(new ScheduleExport($this->search), 'schedules.csv', \Maatwebsite\Excel\Excel::CSV);
            case 'excel':
                return Excel::download(new ScheduleExport($this->search), 'schedules.xlsx');
            case 'pdf':
                // Implement PDF export if needed
                break;
        }
    }

    public function render()
    {
        $user = Auth::user();
        
        // Determine schedules based on user role
        if ($user->isAdmin()) {
            $schedules = Schedule::search($this->search)
                ->sort($this->sortBy, $this->sortDir)
                ->paginate($this->perPage);
        } elseif ($user->isInstructor()) {
            $schedules = Schedule::where('instructor_id', $user->id)
                ->search($this->search)
                ->sort($this->sortBy, $this->sortDir)
                ->paginate($this->perPage);
        } elseif ($user->isStudent()) {
            $schedules = Schedule::where('section_id', $user->section_id)  // Get schedules for the student's section
                ->search($this->search)
                ->sort($this->sortBy, $this->sortDir)
                ->paginate($this->perPage);
        }

        return view('livewire.schedule-table', [
            'schedules' => $schedules,
        ]);
    }

    #[On('refresh-schedule-table')]
    public function refreshScheduleTable()
    {
        $this->schedule = Schedule::all();
    }
}
