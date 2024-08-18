<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Schedule;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceTable extends Component
{
    use WithPagination;

    public $attendance;
    public $title = 'Attendance Records';
    public $event = 'create-attendance';

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $status = '';

    #[Url(history: true)]
    public $sortBy = 'updated_at';

    #[Url(history: true)]
    public $sortDir = 'DESC';

    #[Url()]
    public $perPage = 10;

    #[Url(history: true)]
    public $selectedMonth;

    #[Url(history: true)]
    public $selectedSubject = ''; // Add subject filter

    #[Url(history: true)]
    public $selectedSection = ''; // Add section filter

    public function mount()
    {
        // Set the default value to the current month and year
        $this->selectedMonth = Carbon::now()->format('Y-m');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function clear()
    {
        $this->search = '';
        $this->status = '';
        $this->selectedMonth = Carbon::now()->format('Y-m'); // Reset to current month
        $this->selectedSubject = ''; // Reset the subject filter
        $this->selectedSection = ''; // Reset the section filter
    }

    public function setSortBy($sortByField)
    {
        if ($this->sortBy === $sortByField) {
            $this->sortDir = ($this->sortDir == "ASC") ? 'DESC' : "ASC";
            return;
        }

        $this->sortBy = $sortByField;
        $this->sortDir = 'DESC';
    }

    public function delete(Attendance $attendance)
    {
        $attendance->delete();
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Attendance record deleted successfully');
    }

    public function render()
{
    $query = Attendance::with(['user', 'schedule.subject', 'schedule.section', 'sessions']) // Eager load sessions
                        ->orderBy($this->sortBy, $this->sortDir);

    // Check if the authenticated user is not an admin
    if (!Auth::user()->isAdmin()) {
        // Filter attendance records for the authenticated user only
        $query->where('user_id', Auth::id());
    }

    // Apply search filters
    $query->whereHas('user', function($query) {
        $query->where('first_name', 'like', '%'.$this->search.'%')
              ->orWhere('last_name', 'like', '%'.$this->search.'%');
    });

    // Apply status filter
    if (!empty($this->status)) {
        $query->where('status', $this->status);
    }

    // Apply subject filter
    if (!empty($this->selectedSubject)) {
        $query->whereHas('schedule.subject', function ($query) {
            $query->where('id', $this->selectedSubject);
        });
    }

    // Apply section filter
    if (!empty($this->selectedSection)) {
        $query->whereHas('schedule.section', function ($query) {
            $query->where('id', $this->selectedSection);
        });
    }

    // Apply month filter
    if ($this->selectedMonth) {
        $query->whereMonth('date', Carbon::parse($this->selectedMonth)->month)
              ->whereYear('date', Carbon::parse($this->selectedMonth)->year);
    }

    // Paginate the results
    $attendances = $query->paginate($this->perPage);

    // Get subjects from schedules linked to the authenticated user
    $subjects = Schedule::where(function ($query) {
        $query->where('instructor_id', Auth::id())  // Instructor's subjects
              ->orWhereHas('attendances', function ($q) {
                  $q->where('user_id', Auth::id());  // Student's subjects
              });
    })->with('subject')->get()->pluck('subject')->unique('id');

    // Get sections from schedules linked to the authenticated user
    $sections = Schedule::where(function ($query) {
        $query->where('instructor_id', Auth::id())  // Instructor's sections
              ->orWhereHas('attendances', function ($q) {
                  $q->where('user_id', Auth::id());  // Student's sections
              });
    })->with('section')->get()->pluck('section')->unique('id');

    return view('livewire.attendance-table', [
        'attendances' => $attendances,
        'subjects' => $subjects, 
        'sections' => $sections,
    ]);
}

    #[On('refresh-attendance-table')]
    public function refreshAttendanceTable()
    {
        $this->attendance = Attendance::all();
    }
}
