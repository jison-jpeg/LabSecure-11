<?php

namespace App\Livewire;

use App\Models\Attendance;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class AttendanceStats extends Component
{
    public $scheduleId = null; // Nullable schedule ID for optional filtering
    public $presentCount;
    public $lateCount;
    public $absentCount;
    public $incompleteCount;
    public $filter = 'month';

    public function mount($scheduleId = null)
    {
        $this->scheduleId = $scheduleId;
        $this->loadAttendanceStats();
    }

    public function updatedFilter($value)
    {
        $this->filter = $value;
        $this->loadAttendanceStats();
    }

    public function loadAttendanceStats()
    {
        $query = Attendance::query();

        if ($this->scheduleId) {
            // When scheduleId is provided, filter by schedule
            $query->where('schedule_id', $this->scheduleId)
                ->whereHas('user', function ($query) {
                    $query->where('role_id', 3); // Filter for students only
                });
        } else {
            // Otherwise, filter by authenticated user's attendance
            $userId = Auth::id();
            $query->where('user_id', $userId);
        }

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
