<?php

namespace App\Livewire;

use App\Models\Attendance;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class AttendanceChart extends Component
{
    public $attendanceData = [];
    public $months = [];

    public function mount()
    {
        $this->loadAttendanceData();
    }

    public function loadAttendanceData()
    {
        $user = Auth::user();
        $now = Carbon::now();

        // Initialize arrays for attendance counts
        $presentCounts = [];
        $lateCounts = [];
        $absentCounts = [];
        $incompleteCounts = [];

        // Loop through the last 7 months including the current month
        for ($i = 6; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $this->months[] = $month->format('F Y');

            // Filter attendance based on user role
            $query = Attendance::query();
            if ($user->isAdmin() || $user->isDean() || $user->isChairperson()) {
                $query->whereHas('user', function ($query) {
                    $query->where('role_id', 2); // Filter for instructors
                });
            } elseif ($user->isInstructor()) {
                $query->whereHas('user', function ($query) use ($user) {
                    $query->where('section_id', $user->section_id); // Filter for students in the instructor's section
                });
            } else {
                $query->where('user_id', $user->id);
            }

            $query->whereMonth('date', $month->month)
                  ->whereYear('date', $month->year);

            // Count attendance statuses
            $presentCounts[] = $query->clone()->where('status', 'present')->count();
            $lateCounts[] = $query->clone()->where('status', 'late')->count();
            $absentCounts[] = $query->clone()->where('status', 'absent')->count();
            $incompleteCounts[] = $query->clone()->where('status', 'incomplete')->count();
        }

        // Assign data to the series
        $this->attendanceData = [
            'present' => $presentCounts,
            'late' => $lateCounts,
            'absent' => $absentCounts,
            'incomplete' => $incompleteCounts,
        ];
    }

    public function render()
    {
        return view('livewire.attendance-chart', [
            'attendanceData' => $this->attendanceData,
            'months' => $this->months,
        ]);
    }
}
