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
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Exports\AttendanceExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AttendanceTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Component Title and Events
    public $title = 'Attendance Records';
    public $event = 'create-attendance';
    public $userId;
    // Search and Sorting
    public $search = '';
    public $status = '';
    public $sortBy = 'date';
    public $sortDir = 'DESC';
    public $perPage = 10;

    // Date Filter
    public $selectedMonth;

    // Filters
    public $selectedCollege = '';
    public $colleges = [];

    public $selectedDepartment = '';
    public $departments = [];

    public $selectedYearLevel = '';
    public $yearLevels = [];

    public $selectedSection = '';
    public $sections = [];

    public $selectedSubject = '';
    public $subjects = [];

    // Query String Bindings
    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'sortBy' => ['except' => 'date'],
        'sortDir' => ['except' => 'DESC'],
        'perPage' => ['except' => 10],
        'selectedMonth' => ['except' => null],
        'selectedCollege' => ['except' => ''],
        'selectedDepartment' => ['except' => ''],
        'selectedYearLevel' => ['except' => ''],
        'selectedSection' => ['except' => ''],
        'selectedSubject' => ['except' => ''],
    ];

    // Event Listeners
    protected $listeners = [
        'refresh-attendance-table' => 'refreshAttendanceTable',
    ];

    /**
     * Component Mounting
     */
    public function mount($userId = null)
    {
        $this->userId = $userId;

        // Initialize the selected month to the current month
        $this->selectedMonth = Carbon::now()->format('Y-m');

        // Initialize filter options based on user role
        $this->initializeFilters();
    }

    /**
     * Initialize filter options based on user role.
     */
    protected function initializeFilters()
    {
        $user = Auth::user();

        // Load Colleges for Admins only
        if ($user->isAdmin()) {
            $this->colleges = College::all();
        }

        // Load Departments based on user role
        if ($user->isAdmin()) {
            // Admin: All Departments
            $this->departments = Department::all();
        } elseif ($user->isDean()) {
            // Dean: Departments within their College
            $this->departments = Department::where('college_id', $user->college_id)->get();
            // Optionally, set selectedCollege based on Dean's college
            $this->selectedCollege = $user->college_id;
        } elseif ($user->isChairperson() || $user->isInstructor()) {
            // Chairperson/Instructor: Specific Department
            $this->selectedDepartment = $user->department_id;
            $this->departments = Department::where('id', $this->selectedDepartment)->get();
        }

        // Load Year Levels based on selected Department
        if ($this->selectedDepartment) {
            $this->yearLevels = Section::where('department_id', $this->selectedDepartment)
                ->distinct()
                ->pluck('year_level')
                ->sort()
                ->values()
                ->toArray();
        } else {
            // If no Department is selected, load all Year Levels
            $this->yearLevels = Section::distinct()->pluck('year_level')->sort()->values()->toArray();
        }

        // Load Sections based on selected Department and Year Level
        $this->updateSections();

        // Load Subjects based on current filters
        $this->updateSubjects();
    }

    /**
     * Update Sections based on selected Department and Year Level
     */
    protected function updateSections()
    {
        if ($this->selectedDepartment && $this->selectedYearLevel) {
            $this->sections = Section::where('department_id', $this->selectedDepartment)
                ->where('year_level', $this->selectedYearLevel)
                ->get();
        } elseif ($this->selectedDepartment) {
            // If only Department is selected without Year Level
            $this->sections = Section::where('department_id', $this->selectedDepartment)
                ->get();
        } else {
            // If no Department is selected, reset Sections
            $this->sections = collect();
        }
    }

    /**
     * Update Subjects based on current filters
     */
    protected function updateSubjects()
    {
        $user = Auth::user();

        $subjectsQuery = Subject::query();

        // Apply role-based restrictions
        if ($user->isAdmin()) {
            if ($this->selectedCollege) {
                $subjectsQuery->whereHas('schedules', function ($q) {
                    $q->where('college_id', $this->selectedCollege);
                });
            }
            if ($this->selectedDepartment) {
                $subjectsQuery->whereHas('schedules', function ($q) {
                    $q->where('department_id', $this->selectedDepartment);
                });
            }
        } elseif ($user->isDean()) {
            if ($this->selectedDepartment) {
                $subjectsQuery->whereHas('schedules', function ($q) {
                    $q->where('department_id', $this->selectedDepartment);
                });
            }
        } elseif ($user->isChairperson() || $user->isInstructor()) {
            $subjectsQuery->whereHas('schedules', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
                if ($user->isInstructor()) {
                    $q->where('instructor_id', $user->id);
                }
            });
        } elseif ($user->isStudent()) {
            // Students: Only their own Section's Subjects
            $subjectsQuery->whereHas('schedules', function ($q) use ($user) {
                $q->where('section_id', $user->section_id);
            });
        }

        // Apply additional filters
        if ($this->selectedSection) {
            $subjectsQuery->whereHas('schedules', function ($q) {
                $q->where('section_id', $this->selectedSection);
            });
        }

        $this->subjects = $subjectsQuery->get();
    }

    /**
     * Event Handler: When College is updated
     */
    public function updatedSelectedCollege($value)
    {
        $user = Auth::user();

        // Only Admins can update selectedCollege
        if (!$user->isAdmin()) {
            return;
        }

        // Reset Department, Year Level, Section, and Subject
        $this->reset(['selectedDepartment', 'selectedYearLevel', 'selectedSection', 'selectedSubject']);

        // Reload Departments based on selected College
        if ($value) {
            $this->departments = Department::where('college_id', $value)->get();
        } else {
            $this->departments = Department::all();
        }

        // Update Year Levels
        $this->yearLevels = Section::where('department_id', $this->selectedDepartment)
            ->distinct()
            ->pluck('year_level')
            ->sort()
            ->values()
            ->toArray();

        // Update Sections and Subjects
        $this->updateSections();
        $this->updateSubjects();
    }

    /**
     * Event Handler: When Department is updated
     */
    public function updatedSelectedDepartment($value)
    {
        $user = Auth::user();

        // Prevent resetting for Instructors and Chairpersons since selectedDepartment is set internally
        if ($user->isInstructor() || $user->isChairperson()) {
            return;
        }

        // Reset Year Level, Section, and Subject
        $this->reset(['selectedYearLevel', 'selectedSection', 'selectedSubject']);

        // Update Year Levels based on selected Department
        if ($value) {
            $this->yearLevels = Section::where('department_id', $value)
                ->distinct()
                ->pluck('year_level')
                ->sort()
                ->values()
                ->toArray();
        } else {
            $this->yearLevels = Section::distinct()->pluck('year_level')->sort()->values()->toArray();
        }

        // Update Sections and Subjects
        $this->updateSections();
        $this->updateSubjects();
    }

    /**
     * Event Handler: When Year Level is updated
     */
    public function updatedSelectedYearLevel($value)
    {
        $user = Auth::user();

        // Reset Section and Subject when Year Level changes
        $this->reset(['selectedSection', 'selectedSubject']);

        // Update Sections based on selected Department and Year Level
        $this->updateSections();

        // Update Subjects based on new filters
        $this->updateSubjects();
    }

    /**
     * Event Handler: When Section is updated
     */
    public function updatedSelectedSection($value)
    {
        // Reset Subject
        $this->reset(['selectedSubject']);

        // Update Subjects based on selected Section
        $this->updateSubjects();
    }

    /**
     * Event Handler: When Subject is updated
     */
    public function updatedSelectedSubject($value)
    {
        // Reset Pagination
        $this->resetPage();
    }

    /**
     * Handle search term updates
     */
    public function updatedSearch()
    {
        // Reset Pagination
        $this->resetPage();
    }

    /**
     * Clear All Filters
     */
    public function clear()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $this->reset([
                'search',
                'status',
                'selectedCollege',
                'selectedDepartment',
                'selectedYearLevel',
                'selectedSection',
                'selectedSubject',
            ]);
        } elseif ($user->isDean()) {
            $this->reset([
                'search',
                'status',
                'selectedDepartment',
                'selectedYearLevel',
                'selectedSection',
                'selectedSubject',
            ]);
        } elseif ($user->isChairperson() || $user->isInstructor()) {
            $this->reset([
                'search',
                'status',
                'selectedYearLevel',
                'selectedSection',
                'selectedSubject',
            ]);
        } elseif ($user->isStudent()) {
            $this->reset([
                'search',
                'status',
                'selectedSubject',
            ]);
        }

        // Reset to current month
        $this->selectedMonth = Carbon::now()->format('Y-m');

        // Re-initialize filters based on user role
        $this->initializeFilters();

        // Reset Pagination
        $this->resetPage();
    }

    /**
     * Toggle Sorting
     */
    public function setSortBy($sortByField)
    {
        if ($this->sortBy === $sortByField) {
            $this->sortDir = ($this->sortDir === "ASC") ? 'DESC' : "ASC";
            return;
        }

        $this->sortBy = $sortByField;
        $this->sortDir = 'DESC';
    }

    /**
     * Delete Attendance Record with Logging
     */
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

        // Delete the attendance record
        $attendance->delete();

        // Notify the user
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Attendance record deleted successfully');

        // Refresh the table
        $this->refreshAttendanceTable();
    }

    /**
     * Export Attendance Records
     */
    public function exportAs($format)
{
    $user = Auth::user(); // Get the authenticated user

    // Instantiate the AttendanceExport with only selectedMonth, selectedSubject, and status
    $export = new AttendanceExport(
        $this->selectedMonth,
        $this->selectedSubject,
        $this->status
    );

    switch ($format) {
        case 'csv':
            return Excel::download($export, 'attendance_' . now()->format('Y_m_d_H_i_s') . '.csv', \Maatwebsite\Excel\Excel::CSV);
        case 'excel':
            return Excel::download($export, 'attendance_' . now()->format('Y_m_d_H_i_s') . '.xlsx');
        case 'pdf':
            // Execute the query to get the data
            $attendances = $export->query()->get();

            // Group attendances by schedule name
            $groupedAttendances = $attendances->groupBy(function($item) {
                return $item->schedule->subject->name; // or another attribute as needed
            });

            $pdf = Pdf::loadView('exports.attendance_report', [
                'user' => $user,
                'selectedMonth' => $this->selectedMonth,
                'groupedAttendances' => $groupedAttendances,
            ])->setPaper('a4', 'portrait'); // Optional: set paper size and orientation

            // Stream the PDF for download
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'attendance_report_' . now()->format('Y_m_d_H_i_s') . '.pdf');

        default:
            // Handle unsupported formats
            notyf()
                ->position('x', 'right')
                ->position('y', 'top')
                ->error('Unsupported export format.');
            break;
    }
}

    /**
     * Render the Component View
     */
    public function render()
    {
        $query = Attendance::with(['user', 'schedule.subject', 'schedule.section', 'sessions'])
            ->orderBy($this->sortBy, $this->sortDir);

        $user = Auth::user();

        // Apply Role-Based Access Control
        if ($user->isAdmin()) {
            // Admin: All attendance records with optional College and Department filters
            if ($this->selectedCollege) {
                $query->whereHas('user', function ($q) {
                    $q->where('college_id', $this->selectedCollege);
                });
            }

            if ($this->selectedDepartment) {
                $query->whereHas('user', function ($q) {
                    $q->where('department_id', $this->selectedDepartment);
                });
            }
        } elseif ($user->isDean()) {
            // Dean: Attendances within their College
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('college_id', $user->college_id);
            });
        } elseif ($user->isChairperson()) {
            // Chairperson: Attendances within their Department
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        } elseif ($user->isInstructor()) {
            // Instructor: Only their own attendance records
            $query->where('user_id', $user->id);
        } else {
            // Other users (students): Only their own attendance records
            $query->where('user_id', $user->id);
        }

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }
    

        // Apply Search Filters
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->whereHas('user', function ($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('middle_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%')
                        ->orWhere('suffix', 'like', '%' . $this->search . '%')
                        ->orWhere('username', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                })
                    ->orWhereHas('schedule.subject', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('schedule', function ($q) {
                        $q->where('schedule_code', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('date', 'like', '%' . $this->search . '%')
                    ->orWhere('status', 'like', '%' . $this->search . '%');
            });
        }

        // Apply Status Filter
        if (!empty($this->status)) {
            $query->where('status', strtolower($this->status));
        }

        // Apply Subject Filter
        if (!empty($this->selectedSubject)) {
            $query->whereHas('schedule.subject', function ($q) {
                $q->where('id', $this->selectedSubject);
            });
        }

        // Apply Section Filter
        if (!empty($this->selectedSection)) {
            $query->whereHas('schedule.section', function ($q) {
                $q->where('id', $this->selectedSection);
            });
        }

        // Apply Month Filter
        if ($this->selectedMonth) {
            try {
                $parsedMonth = Carbon::parse($this->selectedMonth);
                $query->whereMonth('date', $parsedMonth->month)
                    ->whereYear('date', $parsedMonth->year);
            } catch (\Exception $e) {
                // Handle invalid date format if necessary
            }
        }

        // Paginate Results
        $attendances = $query->paginate($this->perPage);

        // Return the view with necessary data
        return view('livewire.attendance-table', [
            'attendances' => $attendances,
            'subjects' => $this->subjects,
            'sections' => $this->sections,
            'departments' => $this->departments,
            'colleges' => $this->colleges,
            'yearLevels' => $this->yearLevels,
        ]);
    }

    /**
     * Refresh the Attendance Table
     */
    public function refreshAttendanceTable()
    {
        // Reset Pagination to refresh data
        $this->resetPage();
    }
}
