<?php

namespace App\Livewire;

use App\Models\Attendance;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class AttendanceTable extends Component
{
    use WithPagination;

    public $attendance;
    public $title = 'Attendance Records';
    public $event = 'create-attendance';

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $status = '';

    #[Url(history: true)]
    public $sortBy = 'date';

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
        $this->status = '';
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

    public function delete(Attendance $attendance)
    {
        $attendance->delete();
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Attendance record deleted successfully');
    }

    public function render()
    {
        return view('livewire.attendance-table', [
            'attendances' => Attendance::with(['user', 'schedule'])
                ->whereHas('user', function($query) {
                    $query->where('first_name', 'like', '%'.$this->search.'%')
                          ->orWhere('last_name', 'like', '%'.$this->search.'%');
                })
                ->when($this->status !== '', function ($query) {
                    $query->where('status', $this->status);
                })
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate($this->perPage),
        ]);
    }

    #[On('refresh-attendance-table')]
    public function refreshAttendanceTable()
    {
        $this->attendance = Attendance::all();
    }
}
