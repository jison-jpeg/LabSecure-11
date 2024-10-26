<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class AttendanceRank extends Component
{
    public $attendanceData = [];

    public function mount()
    {
        $this->loadAttendanceRank();
    }

    public function loadAttendanceRank()
    {
        $user = Auth::user();

        // Base query to get instructor attendance data
        $query = User::query()
            ->whereHas('role', function ($q) {
                $q->where('name', 'instructor');
            })
            ->withCount([
                'attendances as present_count' => function ($q) {
                    $q->where('status', 'present');
                },
                'attendances as late_count' => function ($q) {
                    $q->where('status', 'late');
                },
                'attendances as absent_count' => function ($q) {
                    $q->where('status', 'absent');
                },
                'attendances as incomplete_count' => function ($q) {
                    $q->where('status', 'incomplete');
                }
            ]);

        // Role-based filtering
        if ($user->isAdmin()) {
            // Admins see all instructors
            $this->attendanceData = $query->get();
        } elseif ($user->isChairperson()) {
            // Chairpersons see instructors from their department
            $this->attendanceData = $query->where('department_id', $user->department_id)->get();
        } elseif ($user->isDean()) {
            // Deans see instructors from their college
            $this->attendanceData = $query->where('college_id', $user->college_id)->get();
        }
    }

    public function render()
    {
        return view('livewire.attendance-rank', [
            'attendanceData' => $this->attendanceData
        ]);
    }
}
