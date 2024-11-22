<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Attendance;
use Livewire\Component;

class ViewAttendance extends Component
{
    public $user;
    public $presentCount;
    public $absentCount;
    public $lateCount;
    public $incompleteCount;

    public function mount(User $user)
    {
        $this->user = $user;

        // Attendance summary counts
        $this->presentCount = Attendance::where('user_id', $user->id)->where('status', 'present')->count();
        $this->absentCount = Attendance::where('user_id', $user->id)->where('status', 'absent')->count();
        $this->lateCount = Attendance::where('user_id', $user->id)->where('status', 'late')->count();
        $this->incompleteCount = Attendance::where('user_id', $user->id)->where('status', 'incomplete')->count();
    }

    public function render()
    {
        return view('livewire.view-attendance');
    }
}
