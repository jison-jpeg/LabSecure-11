<?php

namespace App\Livewire;

use App\Models\Attendance;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

    #[Url(history: true)]
    public $selectedMonth;

    public function mount()
    {
        // Set the default value to the current month and year
        $this->selectedMonth = Carbon::now()->format('Y-m');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function clear()
    {
        $this->search = '';
        $this->status = '';
        $this->selectedMonth = Carbon::now()->format('Y-m'); // Reset to current month
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
        $query = Attendance::with(['user', 'schedule']);

        // Check if the authenticated user is not an admin
        if (!Auth::user()->isAdmin()) {
            // Filter attendance records for the authenticated user only
            $query->where('user_id', Auth::id());
        }

        // Apply search filters
        $query->whereHas('user', function($query) {
            $query->where('first_name', 'like', '%'.$this->search.'%')
                  ->orWhere('last_name', 'like', '%'.$this->search.'%');
        });

        // Apply status filter
        $query->when($this->status !== '', function ($query) {
            $query->where('status', $this->status);
        });

        // Apply month filter
        if ($this->selectedMonth) {
            $query->whereMonth('date', Carbon::parse($this->selectedMonth)->month)
                  ->whereYear('date', Carbon::parse($this->selectedMonth)->year);
        }

        // Apply sorting
        $attendances = $query->orderBy($this->sortBy, $this->sortDir)
                             ->paginate($this->perPage);

        return view('livewire.attendance-table', [
            'attendances' => $attendances,
        ]);
    }

    #[On('refresh-attendance-table')]
    public function refreshAttendanceTable()
    {
        $this->attendance = Attendance::all();
    }
}
