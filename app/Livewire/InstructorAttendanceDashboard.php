<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Section;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class InstructorAttendanceDashboard extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $selectedSchedule = '';  // Class selection
    public $selectedDate = '';  // Unfiltered by default
    public $status = '';  // Attendance status filter
    public $search = '';  // Search through classes or subjects
    public $perPage = 10;  // Pagination
    public $selectedSubject = '';  // Subject filter
    public $selectedSection = '';  // Section filter
    public $overview = [];

    public $sortBy = 'date';  // Default column to sort by
    public $sortDir = 'DESC'; // Default sorting direction

    public function mount()
    {
        $this->loadOverview();
    }

    public function clear()
    {
        $this->selectedSchedule = '';
        $this->selectedDate = '';
        $this->status = '';
        $this->search = '';
        $this->selectedSubject = '';
        $this->selectedSection = '';
    }

    public function updatedSelectedSchedule()
    {
        $this->resetPage();
        $this->loadOverview(); // Update the overview stats when the schedule changes
    }

    public function updatedSelectedSubject()
    {
        $this->resetPage();
        $this->loadOverview(); // Update overview when subject filter changes
    }

    public function updatedSelectedSection()
    {
        $this->resetPage();
        $this->loadOverview();  // Update overview when section filter changes
    }

    public function updatedSelectedDate()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    // Set sorting criteria
    public function setSortBy($sortByField)
    {
        if ($this->sortBy === $sortByField) {
            $this->sortDir = ($this->sortDir == "ASC") ? 'DESC' : "ASC";
            return;
        }

        $this->sortBy = $sortByField;
        $this->sortDir = 'DESC';
    }

    // Load overview of instructor's attendance (total present, absent, etc.)
    public function loadOverview()
    {
        $attendanceStats = Attendance::where('user_id', Auth::id())  // Fetch only instructor's attendance
            ->when($this->selectedSchedule, function ($query) {
                $query->where('schedule_id', $this->selectedSchedule);
            })
            ->when($this->selectedSubject, function ($query) {
                $query->whereHas('schedule.subject', function ($q) {
                    $q->where('id', $this->selectedSubject);
                });
            })
            ->when($this->selectedSection, function ($query) {
                $query->whereHas('schedule.section', function ($q) {
                    $q->where('id', $this->selectedSection);
                });
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
        // Get all subjects and sections for filtering
        $subjects = Subject::all();
        $sections = Section::all();

        // Get all schedules for the authenticated instructor
        $schedules = Schedule::where('instructor_id', Auth::id())->get();

        // Query attendance for the instructor
        $attendancesQuery = Attendance::where('user_id', Auth::id())  // Instructor's own attendance
            ->when($this->selectedSchedule, function ($query) {
                $query->where('schedule_id', $this->selectedSchedule);
            })
            ->when($this->selectedSubject, function ($query) {
                $query->whereHas('schedule.subject', function ($q) {
                    $q->where('id', $this->selectedSubject);
                });
            })
            ->when($this->selectedSection, function ($query) {
                $query->whereHas('schedule.section', function ($q) {
                    $q->where('id', $this->selectedSection);
                });
            })
            ->when($this->selectedDate, function ($query) {
                $query->whereDate('date', $this->selectedDate);
            })
            ->when(!empty($this->status), function ($query) {
                $query->where('status', $this->status);
            });

        // Apply sorting
        if ($this->sortBy === 'schedule.schedule_code') {
            $attendancesQuery->join('schedules', 'attendances.schedule_id', '=', 'schedules.id')
                ->orderBy('schedules.schedule_code', $this->sortDir)
                ->select('attendances.*');
        } else {
            $attendancesQuery->orderBy($this->sortBy, $this->sortDir);
        }

        $attendances = $attendancesQuery->paginate($this->perPage);

        return view('livewire.instructor-attendance-dashboard', [
            'schedules' => $schedules,
            'subjects' => $subjects,
            'sections' => $sections,
            'attendances' => $attendances,
        ]);
    }
}
