<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceStats extends Component
{
    public $presentCount;
    public $lateCount;
    public $absentCount;
    public $incompleteCount;
    public $filter = 'month';

    public function mount()
    {
        $this->loadAttendanceStats();
    }

    public function updatedFilter($value)
    {
        $this->filter = $value;
        $this->loadAttendanceStats();
    }

    public function loadAttendanceStats()
    {
        $userId = Auth::id();

        $query = Attendance::where('user_id', $userId);

        switch ($this->filter) {
            case 'today':
                $query->whereDate('date', Carbon::today());
                break;
            case 'month':
                $query->whereMonth('date', Carbon::now()->month)
                      ->whereYear('date', Carbon::now()->year);
                break;
            case 'year':
                $query->whereYear('date', Carbon::now()->year);
                break;
        }

        $this->presentCount = $query->clone()->where('status', 'present')->count();
        $this->lateCount = $query->clone()->where('status', 'late')->count();
        $this->absentCount = $query->clone()->where('status', 'absent')->count();
        $this->incompleteCount = $query->clone()->where('status', 'incomplete')->count();
    }

    public function render()
    {
        return view('livewire.attendance-stats');
    }
}