<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\TransactionLog;
use App\Models\Subject;
use App\Models\Section;
use App\Models\College;
use App\Models\Department;
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

    // Admin Filters
    public $selectedCollege = '';
    public $colleges = [];

    public $selectedDepartment = '';
    public $departments = [];

    // Common Filters
    public $selectedSection = '';
    public $sections = [];

    public $selectedSubject = '';
    public $subjects = [];

    public function mount()
    {
        // Set the default value to the current month and year
        $this->selectedMonth = Carbon::now()->format('Y-m');

        // Load colleges if user is Admin
        if (Auth::user()->isAdmin()) {
            $this->colleges = College::all();
        }
    }

    public function updatedSelectedCollege()
    {
        $this->reset(['selectedDepartment', 'selectedSection', 'selectedSubject']);

        if ($this->selectedCollege) {
            $this->departments = Department::where('college_id', $this->selectedCollege)->get();
        } else {
            $this->departments = [];
        }

        $this->sections = [];
        $this->subjects = [];
    }

    public function updatedSelectedDepartment()
    {
        $this->reset(['selectedSection', 'selectedSubject']);

        if (Auth::user()->isAdmin()) {
            if ($this->selectedDepartment) {
                $this->sections = Section::where('department_id', $this->selectedDepartment)->get();
                $this->subjects = Subject::whereHas('schedules', function ($q) {
                    $q->where('department_id', $this->selectedDepartment);
                })->get();
            } else {
                $this->sections = [];
                $this->subjects = [];
            }
        } elseif (Auth::user()->isDean() || Auth::user()->isChairperson()) {
            if ($this->selectedDepartment) {
                $this->sections = Section::where('department_id', $this->selectedDepartment)->get();
                $this->subjects = Subject::whereHas('schedules', function ($q) {
                    $q->where('department_id', Auth::user()->department_id);
                })->get();
            } else {
                $this->sections = [];
                $this->subjects = [];
            }
        }
    }

    public function updatedSelectedSection()
    {
        $this->reset(['selectedSubject']);

        if ($this->selectedSection) {
            if (Auth::user()->isAdmin()) {
                $this->subjects = Subject::whereHas('schedules', function ($q) {
                    $q->where('college_id', $this->selectedCollege)
                      ->orWhere('department_id', $this->selectedDepartment);
                })->get();
            } elseif (Auth::user()->isChairperson()) {
                $this->subjects = Subject::whereHas('schedules', function ($q) {
                    $q->where('department_id', Auth::user()->department_id);
                })->get();
            } elseif (Auth::user()->isInstructor()) {
                $this->subjects = Subject::whereHas('schedules', function ($q) {
                    $q->where('instructor_id', Auth::id());
                })->get();
            }
        } else {
            $this->subjects = [];
        }
    }

    public function updatedSelectedSubject()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function clear()
    {
        $this->reset([
            'search',
            'status',
            'selectedMonth',
            'selectedCollege',
            'selectedDepartment',
            'selectedSection',
            'selectedSubject',
            'departments',
            'sections',
            'subjects',
        ]);

        // Reset to current month
        $this->selectedMonth = Carbon::now()->format('Y-m');

        // Reload colleges if Admin
        if (Auth::user()->isAdmin()) {
            $this->colleges = College::all();
        }
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
            if ($this->selectedCollege) {
                $query->whereHas('user', function ($q) use ($user) {
                    $q->where('college_id', $user->college_id);
                });
            }
            if ($this->selectedDepartment) {
                $query->whereHas('user', function ($q) use ($user) {
                    $q->where('department_id', $this->selectedDepartment);
                });
            }
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

        // Fetch filters based on user role
        if ($user->isAdmin()) {
            // Admin: Colleges, Departments, Sections, Subjects
            $colleges = College::all();
            $departments = $this->selectedCollege ? Department::where('college_id', $this->selectedCollege)->get() : collect();
            $sections = $this->selectedDepartment ? Section::where('department_id', $this->selectedDepartment)->get() : collect();
            $subjects = ($this->selectedCollege || $this->selectedDepartment) ? Subject::whereHas('schedules', function ($q) use ($user) {
                if ($this->selectedCollege) {
                    $q->where('college_id', $this->selectedCollege);
                }
                if ($this->selectedDepartment) {
                    $q->where('department_id', $this->selectedDepartment);
                }
            })->get() : Subject::all();
        } elseif ($user->isDean()) {
            // Dean: Departments, Sections, Subjects
            $departments = Department::where('college_id', $user->college_id)->get();
            $sections = $this->selectedDepartment ? Section::where('department_id', $this->selectedDepartment)->get() : collect();
            $subjects = $this->selectedDepartment ? Subject::whereHas('schedules', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            })->get() : collect();
        } elseif ($user->isChairperson()) {
            // Chairperson: Sections, Subjects
            $sections = Section::where('department_id', $user->department_id)->get();
            $subjects = Subject::whereHas('schedules', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            })->get();
        } elseif ($user->isInstructor()) {
            // Instructor: Sections, Subjects
            $sections = Section::whereHas('schedules', function ($q) use ($user) {
                $q->where('instructor_id', $user->id);
            })->get();
            $subjects = Subject::whereHas('schedules', function ($q) use ($user) {
                $q->where('instructor_id', $user->id);
            })->get();
        } elseif ($user->isStudent()) {
            // Student: Subjects
            $subjects = Subject::whereHas('schedules.attendances', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->get();
            $sections = collect();
            $departments = collect();
            $colleges = collect();
        } else {
            // Any other roles: Default to all subjects and sections
            $colleges = College::all();
            $departments = Department::all();
            $sections = Section::all();
            $subjects = Subject::all();
        }

        return view('livewire.attendance-table', [
            'attendances' => $attendances,
            'subjects' => $subjects,
            'sections' => $sections,
            'departments' => isset($departments) ? $departments : collect(),
            'colleges' => isset($colleges) ? $colleges : collect(),
        ]);
    }

    #[On('refresh-attendance-table')]
    public function refreshAttendanceTable()
    {
        $this->attendance = Attendance::all();
    }
}
