<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Schedule;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class SubjectAttendanceManagement extends Component
{
    use WithPagination;

    public $schedule;
    public $selectedDate = '';  // Date filter (empty for unfiltered)
    public $status = '';        // Attendance status filter
    public $search = '';        // Search for students
    public $perPage = 10;       // Pagination
    public $overview = [];      // Attendance overview statistics

    public function mount(Schedule $schedule)
    {
        $this->schedule = $schedule;
        $this->loadOverview();
    }

    public function loadOverview()
    {
        // Get attendance statistics for the selected schedule (students only)
        $attendanceStats = Attendance::where('schedule_id', $this->schedule->id)
            ->whereHas('user', function ($query) {
                // Use the isStudent method to filter students
                $query->whereHas('role', function ($roleQuery) {
                    $roleQuery->where('name', 'student');
                });
            })
            ->when($this->selectedDate, function ($query) {
                $query->whereDate('date', Carbon::parse($this->selectedDate));
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
        // Query only students' attendance records for the selected schedule
        $studentsQuery = Attendance::where('schedule_id', $this->schedule->id)
            ->whereHas('user', function ($query) {
                // Filter for students using the isStudent method
                $query->whereHas('role', function ($roleQuery) {
                    $roleQuery->where('name', 'student');
                });
            })
            ->when($this->selectedDate, function ($query) {
                // Apply date filter if selected
                $query->whereDate('date', $this->selectedDate);
            });

        if ($this->status) {
            $studentsQuery->where('status', $this->status);
        }

        if ($this->search) {
            $studentsQuery->whereHas('user', function ($query) {
                $query->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('username', 'like', '%' . $this->search . '%');
            });
        }

        // Fetch paginated results
        $students = $studentsQuery->paginate($this->perPage);

        return view('livewire.subject-attendance-management', [
            'students' => $students,
            'overview' => $this->overview,
        ]);
    }
}
