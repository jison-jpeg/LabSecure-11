<?php

namespace App\Livewire;

use App\Models\Attendance;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceSummary extends Component
{
    public $present = [];
    public $late = [];
    public $absent = [];
    public $incomplete = [];
    public $months = [];

    public function mount()
    {
        $this->loadAttendanceSummary();
    }

    public function loadAttendanceSummary()
    {
        $currentUserId = Auth::id();
        $startMonth = Carbon::now()->subMonths(5);

        // Initialize arrays for the last 6 months (including current)
        for ($i = 0; $i <= 5; $i++) {
            $month = $startMonth->copy()->addMonths($i);
            $this->months[] = $month->shortMonthName;

            $this->present[] = Attendance::where('user_id', $currentUserId)
                ->whereMonth('date', $month->month)
                ->whereYear('date', $month->year)
                ->where('status', 'present')
                ->count();

            $this->late[] = Attendance::where('user_id', $currentUserId)
                ->whereMonth('date', $month->month)
                ->whereYear('date', $month->year)
                ->where('status', 'late')
                ->count();

            $this->absent[] = Attendance::where('user_id', $currentUserId)
                ->whereMonth('date', $month->month)
                ->whereYear('date', $month->year)
                ->where('status', 'absent')
                ->count();

            $this->incomplete[] = Attendance::where('user_id', $currentUserId)
                ->whereMonth('date', $month->month)
                ->whereYear('date', $month->year)
                ->where('status', 'incomplete')
                ->count();
        }
    }

    public function render()
    {
        return view('livewire.attendance-summary', [
            'present' => $this->present,
            'late' => $this->late,
            'absent' => $this->absent,
            'incomplete' => $this->incomplete,
            'months' => $this->months,
        ]);
    }
}
