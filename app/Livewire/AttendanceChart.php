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

            // Query attendance records for the authenticated user for the specific month and year
            $query = Attendance::where('user_id', $user->id)
                               ->whereMonth('date', $month->month)
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
