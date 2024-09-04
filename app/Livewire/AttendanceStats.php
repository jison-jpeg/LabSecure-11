<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;

class AttendanceStats extends Component
{
    public $presentCount;
    public $lateCount;
    public $absentCount;
    public $incompleteCount;

    public function mount()
    {
        $this->loadAttendanceStats();
    }

    public function loadAttendanceStats()
    {
        $userId = Auth::id();

        $this->presentCount = Attendance::where('user_id', $userId)->where('status', 'present')->count();
        $this->lateCount = Attendance::where('user_id', $userId)->where('status', 'late')->count();
        $this->absentCount = Attendance::where('user_id', $userId)->where('status', 'absent')->count();
        $this->incompleteCount = Attendance::where('user_id', $userId)->where('status', 'incomplete')->count();
    }

    public function render()
    {
        return view('livewire.attendance-stats');
    }
}
