<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\College;
use App\Models\Department;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TransactionLog;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Exports\AttendanceExport;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filter Properties
    public $search = '';
    public $status = '';
    public $sortBy = 'date';
    public $sortDir = 'DESC';
    public $perPage = 10;
    public $selectedMonth;
    public $selectedCollege = '';      // For Admin
    public $selectedDepartment = '';   // For Admin & Dean
    public $selectedSection = '';      // For Admin, Dean, Chairperson, Instructor
    public $selectedSubject = '';      // For Admin, Dean, Chairperson, Instructor, Student

    // Listener for Refresh Event
    protected $listeners = ['refreshAttendanceTable'];

    public function mount()
    {
        // Set the default value to the current month and year
        $this->selectedMonth = Carbon::now()->format('Y-m');
    }

    // Reset Pagination on Search Update
    public function updatedSearch()
    {
        $this->resetPage();
    }

    // Reset Pagination and Dependent Filters on Parent Filter Updates
    public function updatedSelectedCollege()
    {
        $this->resetPage();
        $this->selectedDepartment = '';
        $this->selectedSection = '';
        $this->selectedSubject = '';
    }

    public function updatedSelectedDepartment()
    {
        $this->resetPage();
        $this->selectedSection = '';
        $this->selectedSubject = '';
    }

    public function updatedSelectedSection()
    {
        $this->resetPage();
        $this->selectedSubject = '';
    }

    public function updatedSelectedSubject()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function updatedSelectedMonth()
    {
        $this->resetPage();
    }

    // Clear All Filters
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
        ]);

        // Reset to current month after clearing
        $this->selectedMonth = Carbon::now()->format('Y-m');
    }

    // Toggle Sorting
    public function setSortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDir = $this->sortDir === 'ASC' ? 'DESC' : 'ASC';
        } else {
            $this->sortBy = $field;
            $this->sortDir = 'DESC';
        }
    }

    // Delete Attendance Record
    public function delete(Attendance $attendance)
    {
        // Authorization Check
        if (!Auth::user()->can('delete', $attendance)) {
            abort(403, 'Unauthorized action.');
        }

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

        // Delete the record
        $attendance->delete();

        // Success Notification
        session()->flash('message', 'Attendance record deleted successfully.');
    }

    // Export Attendance Records
    public function exportAs($format)
    {
        // Determine export format
        $export = new AttendanceExport(
            $this->selectedMonth,
            $this->selectedCollege,
            $this->selectedDepartment,
            $this->selectedSection,
            $this->selectedSubject,
            $this->status,
            $this->search,
            Auth::user()
        );

        $fileName = 'attendance_' . now()->format('Y_m_d_H_i_s');

        switch ($format) {
            case 'csv':
                return Excel::download($export, $fileName . '.csv', \Maatwebsite\Excel\Excel::CSV);
            case 'excel':
                return Excel::download($export, $fileName . '.xlsx');
            case 'pdf':
                // Implement PDF export if needed
                break;
            default:
                return redirect()->back()->with('error', 'Invalid export format.');
        }
    }

    // Render Method
    public function render()
    {
        $query = Attendance::with(['user.college', 'user.department', 'schedule.subject', 'schedule.section'])
            ->orderBy($this->sortBy, $this->sortDir);

        // Fetch the authenticated user
        $user = Auth::user();

        // Apply role-based filters to attendance records
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
            $query->where('status', strtolower($this->status));
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

        // Fetch filter options based on user role
        if ($user->isAdmin()) {
            // Admin: All Colleges, Departments based on selectedCollege, Sections based on selectedDepartment, Subjects based on selectedCollege or selectedDepartment
            $colleges = College::all();

            // Departments based on selectedCollege
            $departments = $this->selectedCollege
                ? Department::where('college_id', $this->selectedCollege)->get()
                : Department::all();

            // Sections based on selectedDepartment
            $sections = $this->selectedDepartment
                ? Section::where('department_id', $this->selectedDepartment)->get()
                : Section::all();

            // Subjects based on selectedCollege or selectedDepartment
            $subjects = Subject::where(function ($q) {
                    if ($this->selectedCollege) {
                        $q->where('college_id', $this->selectedCollege);
                    }
                })
                ->orWhere(function ($q) {
                    if ($this->selectedDepartment) {
                        $q->where('department_id', $this->selectedDepartment);
                    }
                })
                ->distinct()
                ->get();
        } elseif ($user->isDean()) {
            // Dean: Departments within their college, Sections based on selectedDepartment, Subjects based on selectedDepartment
            $colleges = null; // Not applicable for Dean

            $departments = Department::where('college_id', $user->college_id)->get();

            $sections = $this->selectedDepartment
                ? Section::where('department_id', $this->selectedDepartment)->get()
                : Section::where('college_id', $user->college_id)->get();

            // Subject filter based on selectedDepartment
            if ($this->selectedDepartment) {
                $subjects = Subject::where('department_id', $this->selectedDepartment)->get();
            } else {
                // If no department is selected, show all subjects within the dean's college
                $subjects = Subject::where('college_id', $user->college_id)->get();
            }
        } elseif ($user->isChairperson()) {
            // Chairperson: Sections within their department, Subjects based on their department's college
            $colleges = null; // Not applicable for Chairperson
            $departments = null; // Not applicable for Chairperson

            $sections = Section::where('department_id', $user->department_id)->get();

            // Assuming Subject is linked to Department via schedules
            $subjects = Subject::whereHas('schedules', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            })->distinct()->get();
        } elseif ($user->isInstructor()) {
            // Instructor: Sections and Subjects based on their assigned schedules
            $colleges = null; // Not applicable for Instructor
            $departments = null; // Not applicable for Instructor

            $sections = Section::whereHas('schedules', function ($q) use ($user) {
                $q->where('instructor_id', $user->id);
            })->distinct()->get();

            $subjects = Subject::whereHas('schedules', function ($q) use ($user) {
                $q->where('instructor_id', $user->id);
            })->distinct()->get();
        } elseif ($user->isStudent()) {
            // Student: Only their Subjects
            $colleges = null;
            $departments = null;
            $sections = null; // Not applicable for Student

            $subjects = Subject::whereHas('schedules.attendances', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->distinct()->get();
        } else {
            // Any other roles: Default to all subjects and sections or restrict as needed
            $colleges = College::all();
            $departments = Department::all();
            $sections = Section::all();
            $subjects = Subject::all();
        }

        return view('livewire.attendance-table', [
            'attendances' => $attendances,
            'colleges' => $colleges ?? null,
            'departments' => $departments ?? null,
            'sections' => $sections ?? null,
            'subjects' => $subjects ?? null,
            'user' => $user,
        ]);
    }

    // Listener Method to Refresh Attendance Table
    public function refreshAttendanceTable()
    {
        $this->resetPage();
    }
}
