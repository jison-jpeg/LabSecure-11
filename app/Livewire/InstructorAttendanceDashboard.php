<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class InstructorAttendanceDashboard extends Component
{
    use WithPagination;

    public $selectedSchedule = '';  // Class selection
    public $selectedDate = '';  // Unfiltered by default
    public $status = '';  // Attendance status filter
    public $search = '';  // Search through classes or subjects
    public $perPage = 10;  // Pagination
    public $overview = [];

    public function mount()
    {
        $this->loadOverview();
    }

    public function updatedSelectedSchedule()
    {
        $this->resetPage();
        $this->loadOverview();  // Update the overview stats when the schedule changes
    }

    public function updatedSelectedDate()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    // Load overview of instructor's attendance (total present, absent, etc.)
    public function loadOverview()
    {
        $attendanceStats = Attendance::where('user_id', Auth::id())  // Fetch only instructor's attendance
            ->when($this->selectedSchedule, function ($query) {
                $query->where('schedule_id', $this->selectedSchedule);
            })
            ->when($this->selectedDate, function ($query) {
                $query->whereDate('date', $this->selectedDate);
            })
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $this->overview = [
            'present' => $attendanceStats['present'] ?? 0,
            'absent' => $attendanceStats['absent'] ?? 0,
            'late' => $attendanceStats['late'] ?? 0,
            'incomplete' => $attendanceStats['incomplete'] ?? 0,
        ];
    }

    public function render()
    {
        // Get all schedules for the authenticated instructor
        $schedules = Schedule::where('instructor_id', Auth::id())->get();

        // Query attendance for the instructor
        $attendancesQuery = Attendance::where('user_id', Auth::id())  // Instructor's own attendance
            ->when($this->selectedSchedule, function ($query) {
                $query->where('schedule_id', $this->selectedSchedule);
            });

        // Apply date filter only if a date is selected
        if ($this->selectedDate) {
            $attendancesQuery->whereDate('date', $this->selectedDate);
        }

        // Apply status filter
        if (!empty($this->status)) {
            $attendancesQuery->where('status', $this->status);
        }

        $attendances = $attendancesQuery->paginate($this->perPage);

        return view('livewire.instructor-attendance-dashboard', [
            'schedules' => $schedules,
            'attendances' => $attendances,
        ]);
    }
}
