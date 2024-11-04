<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class ViewAttendance extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $user;
    public $perPage = 10;
    public $search = '';
    public $status = '';
    public $subject = '';
    public $scheduleCode = '';

    public $subjects = [];
    public $scheduleCodes = [];

    // Attendance counts
    public $presentCount;
    public $absentCount;
    public $lateCount;
    public $incompleteCount;

    public function mount(User $user)
    {
        $this->user = $user;

        // Load subjects and schedule codes based on user's attendance records
        $this->subjects = Schedule::join('subjects', 'subjects.id', '=', 'schedules.subject_id')
            ->whereHas('attendances', function ($query) {
                $query->where('user_id', $this->user->id);
            })
            ->pluck('subjects.name', 'subjects.id')
            ->toArray();

        $this->scheduleCodes = Schedule::whereHas('attendances', function ($query) {
                $query->where('user_id', $this->user->id);
            })
            ->pluck('schedule_code', 'id')
            ->toArray();

        $this->calculateAttendanceStats();
    }

    public function calculateAttendanceStats()
    {
        // Calculate attendance stats for present, absent, late, and incomplete
        $this->presentCount = Attendance::where('user_id', $this->user->id)
            ->when($this->subject, function ($query) {
                $query->whereHas('schedule', function ($q) {
                    $q->where('subject_id', $this->subject);
                });
            })
            ->when($this->scheduleCode, function ($query) {
                $query->whereHas('schedule', function ($q) {
                    $q->where('schedule_code', $this->scheduleCode);
                });
            })
            ->where('status', 'present')
            ->count();

        $this->absentCount = Attendance::where('user_id', $this->user->id)
            ->when($this->subject, function ($query) {
                $query->whereHas('schedule', function ($q) {
                    $q->where('subject_id', $this->subject);
                });
            })
            ->when($this->scheduleCode, function ($query) {
                $query->whereHas('schedule', function ($q) {
                    $q->where('schedule_code', $this->scheduleCode);
                });
            })
            ->where('status', 'absent')
            ->count();

        $this->lateCount = Attendance::where('user_id', $this->user->id)
            ->when($this->subject, function ($query) {
                $query->whereHas('schedule', function ($q) {
                    $q->where('subject_id', $this->subject);
                });
            })
            ->when($this->scheduleCode, function ($query) {
                $query->whereHas('schedule', function ($q) {
                    $q->where('schedule_code', $this->scheduleCode);
                });
            })
            ->where('status', 'late')
            ->count();

        $this->incompleteCount = Attendance::where('user_id', $this->user->id)
            ->when($this->subject, function ($query) {
                $query->whereHas('schedule', function ($q) {
                    $q->where('subject_id', $this->subject);
                });
            })
            ->when($this->scheduleCode, function ($query) {
                $query->whereHas('schedule', function ($q) {
                    $q->where('schedule_code', $this->scheduleCode);
                });
            })
            ->where('status', 'incomplete')
            ->count();
    }

    public function clear()
    {
        $this->search = '';
        $this->status = '';
        $this->subject = '';
        $this->scheduleCode = '';
        $this->calculateAttendanceStats();
    }

    public function getAttendanceProperty()
    {
        return Attendance::where('user_id', $this->user->id)
            ->when($this->search, function ($query) {
                $query->whereHas('schedule.subject', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('schedule', function ($q) {
                    $q->where('schedule_code', 'like', '%' . $this->search . '%');
                })
                ->orWhereRaw('DATE_FORMAT(date, "%m/%d/%Y") like ?', ['%' . $this->search . '%']); // Search formatted date
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->subject, function ($query) {
                $query->whereHas('schedule', function ($q) {
                    $q->where('subject_id', $this->subject);
                });
            })
            ->when($this->scheduleCode, function ($query) {
                $query->whereHas('schedule', function ($q) {
                    $q->where('schedule_code', $this->scheduleCode);
                });
            })
            ->orderBy('date', 'desc')
            ->paginate($this->perPage);
    }

    public function updatedSubject()
    {
        $this->calculateAttendanceStats(); // Recalculate stats when subject filter is changed
    }

    public function updatedScheduleCode()
    {
        $this->calculateAttendanceStats(); // Recalculate stats when schedule code filter is changed
    }

    public function render()
    {
        return view('livewire.view-attendance', [
            'attendance' => $this->attendance,
            'subjects' => $this->subjects,
            'scheduleCodes' => $this->scheduleCodes,
        ]);
    }
}
