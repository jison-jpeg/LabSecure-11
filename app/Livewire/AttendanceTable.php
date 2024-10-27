<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\TransactionLog;  // Added TransactionLog model
use App\Models\Subject;          // Added Subject model
use App\Models\Section;          // Added Section model
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Exports\AttendanceExport;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $attendance;
    public $title = 'Attendance Records';
    public $event = 'create-attendance';

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $status = '';

    #[Url(history: true)]
    public $sortBy = 'date';

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
        // Log the deletion
        TransactionLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'model' => 'Attendance',
            'model_id' => $attendance->id,
            'details' => json_encode([
                'user' => $attendance->user->full_name,
                'username' => $attendance->user->username,
                'schedule_id' => $attendance->schedule_id
            ]),
        ]);

        $attendance->delete();

        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Attendance record deleted successfully');
    }

    public function exportAs($format)
    {
        $export = new AttendanceExport($this->selectedMonth, $this->selectedSubject, $this->selectedSection);

        switch ($format) {
            case 'csv':
                return Excel::download($export, 'attendance.csv', \Maatwebsite\Excel\Excel::CSV);
            case 'excel':
                return Excel::download($export, 'attendance.xlsx');
            case 'pdf':
                // Implement PDF export if needed
                break;
        }
    }

    public function render()
    {
        $query = Attendance::with(['user', 'schedule.subject', 'schedule.section', 'sessions'])
            ->orderBy($this->sortBy, $this->sortDir);

        // Fetch the authenticated user
        $user = Auth::user();

        // Apply role-based filters
        if ($user->isAdmin()) {
            // Admin can view all attendance records
            // No additional filtering needed
        } elseif ($user->isDean()) {
            // Dean can view attendances of users within their college
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('college_id', $user->college_id);
            });
        } elseif ($user->isChairperson()) {
            // Chairperson can view attendances of users within their department
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        } elseif ($user->isInstructor()) {
            // Instructor can view attendances of users within their schedules
            $query->whereHas('schedule', function ($q) use ($user) {
                $q->where('instructor_id', $user->id);
            });
        } elseif ($user->isStudent()) {
            // Student can view only their own attendance records
            $query->where('user_id', $user->id);
        } else {
            // For any other roles, default to viewing only their own attendance records
            $query->where('user_id', $user->id);
        }

        // Apply search filters
        if (!empty($this->search)) {
            $query->whereHas('user', function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('middle_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('suffix', 'like', '%' . $this->search . '%')
                  ->orWhere('username', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        // Apply status filter
        if (!empty($this->status)) {
            $query->where('status', $this->status);
        }

        // Apply subject filter
        if (!empty($this->selectedSubject)) {
            $query->whereHas('schedule.subject', function ($q) {
                $q->where('id', $this->selectedSubject);
            });
        }

        // Apply section filter
        if (!empty($this->selectedSection)) {
            $query->whereHas('schedule.section', function ($q) {
                $q->where('id', $this->selectedSection);
            });
        }

        // Apply month filter
        if ($this->selectedMonth) {
            $parsedMonth = Carbon::parse($this->selectedMonth);
            $query->whereMonth('date', $parsedMonth->month)
                  ->whereYear('date', $parsedMonth->year);
        }

        // Paginate the results
        $attendances = $query->paginate($this->perPage);

        // Fetch subjects and sections based on user role for filters
        if ($user->isAdmin()) {
            // Admin: All subjects and sections
            $subjects = Subject::all();
            $sections = Section::all();
        } elseif ($user->isDean()) {
            // Dean: Subjects and sections within their college
            $subjects = Subject::whereHas('schedules', function ($q) use ($user) {
                $q->where('college_id', $user->college_id);
            })->distinct()->get();

            $sections = Section::where('college_id', $user->college_id)->get();
        } elseif ($user->isChairperson()) {
            // Chairperson: Subjects and sections within their department
            $subjects = Subject::whereHas('schedules', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            })->distinct()->get();

            $sections = Section::where('department_id', $user->department_id)->get();
        } elseif ($user->isInstructor()) {
            // Instructor: Subjects and sections they handle
            $subjects = Subject::whereHas('schedules', function ($q) use ($user) {
                $q->where('instructor_id', $user->id);
            })->distinct()->get();

            $sections = Section::whereHas('schedules', function ($q) use ($user) {
                $q->where('instructor_id', $user->id);
            })->distinct()->get();
        } elseif ($user->isStudent()) {
            // Student: Subjects and sections they are enrolled in (based on their attendances)
            $subjects = Subject::whereHas('schedules.attendances', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->distinct()->get();

            $sections = Section::whereHas('schedules.attendances', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->distinct()->get();
        } else {
            // Any other roles: Default to all subjects and sections or restrict as needed
            $subjects = Subject::all();
            $sections = Section::all();
        }

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
