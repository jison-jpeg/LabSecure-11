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

class AttendanceTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $title = 'Attendance Records';
    public $event = 'create-attendance';

    public $search = '';
    public $status = '';
    public $sortBy = 'date';
    public $sortDir = 'DESC';
    public $perPage = 10;
    public $selectedMonth;

    // Filters
    public $selectedCollege = '';
    public $colleges = [];

    public $selectedDepartment = '';
    public $departments = [];

    public $selectedSection = '';
    public $sections = [];

    public $selectedSubject = '';
    public $subjects = [];

    // Define query string bindings
    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'sortBy' => ['except' => 'date'],
        'sortDir' => ['except' => 'DESC'],
        'perPage' => ['except' => 10],
        'selectedMonth' => ['except' => null],
    ];

    // Define event listeners
    protected $listeners = [
        'refresh-attendance-table' => 'refreshAttendanceTable',
    ];

    public function mount()
    {
        // Set the default value to the current month and year
        $this->selectedMonth = Carbon::now()->format('Y-m');

        $this->initializeFilters();
    }

    /**
     * Initialize filter options based on user role.
     */
    protected function initializeFilters()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            // Admin can view all colleges and departments
            $this->colleges = College::all();
            $this->departments = Department::all();
        } elseif ($user->isDean()) {
            // Deans have access to departments within their college
            $this->departments = Department::where('college_id', $user->college_id)->get();
        } elseif ($user->isChairperson()) {
            // Chairpersons have their department set internally
            $this->selectedDepartment = $user->department_id;
            $this->departments = Department::where('id', $this->selectedDepartment)->get();
            $this->sections = Section::where('department_id', $this->selectedDepartment)->get();
            $this->subjects = Subject::whereHas('schedules', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            })->get();
            return; // No need to load further filters for Chairpersons
        } elseif ($user->isInstructor()) {
            // Instructors have their department set internally
            $this->selectedDepartment = $user->department_id;
            $this->departments = Department::where('id', $this->selectedDepartment)->get();
            $this->sections = Section::where('department_id', $this->selectedDepartment)->get();
            $this->subjects = Subject::whereHas('schedules', function ($q) use ($user) {
                $q->where('instructor_id', $user->id);
            })->get();
            return; // No need to load further filters for Instructors
        } elseif ($user->isStudent()) {
            // **Initialize Filters for Students:**
            $this->subjects = Subject::whereHas('schedules', function ($q) use ($user) {
                $q->where('section_id', $user->section_id);
            })->distinct()->get();
            $this->sections = Section::where('id', $user->section_id)->get();
            // Departments and Colleges can be derived from the section if needed
            return; // No need to load further filters for Students
        }

        // For Admins and Deans, load sections and subjects based on selected department
        if ($user->isAdmin() || $user->isDean()) {
            if ($this->selectedDepartment) {
                $this->sections = Section::where('department_id', $this->selectedDepartment)->get();
                $this->subjects = Subject::whereHas('schedules', function ($q) use ($user) {
                    $q->where('department_id', $this->selectedDepartment);

                    // Include section filtering if a section is selected
                    if ($this->selectedSection) {
                        $q->where('section_id', $this->selectedSection);
                    }
                })->get();
            } else {
                $this->sections = collect();
                $this->subjects = collect();
            }
        }
    }

    /**
     * Reset dependent filters when a parent filter is updated.
     */
    public function updatedSelectedCollege()
    {
        $user = Auth::user();

        // Only Admins can update selectedCollege
        if (!$user->isAdmin()) {
            return;
        }

        // Reset dependent filters
        $this->reset(['selectedDepartment', 'selectedSection', 'selectedSubject']);

        // Load departments based on selected college
        $this->departments = $this->selectedCollege
            ? Department::where('college_id', $this->selectedCollege)->get()
            : Department::all(); // Admin can select any department across colleges

        // Reset sections and subjects
        $this->sections = collect();
        $this->subjects = collect();
    }

    public function updatedSelectedDepartment()
    {
        $user = Auth::user();

        // Prevent resetting for Instructors and Chairpersons since selectedDepartment is set internally
        if ($user->isInstructor() || $user->isChairperson()) {
            return;
        }

        // Reset dependent filters
        $this->reset(['selectedSection', 'selectedSubject']);

        // Load sections based on selected department
        $this->sections = $this->selectedDepartment
            ? Section::where('department_id', $this->selectedDepartment)->get()
            : collect();

        // Load subjects based on selected department and section (if any)
        $this->subjects = $this->selectedDepartment
            ? Subject::whereHas('schedules', function ($q) use ($user) {
                $q->where('department_id', $this->selectedDepartment);

                // Include section filtering if a section is selected
                if ($this->selectedSection) {
                    $q->where('section_id', $this->selectedSection);
                }
            })->get()
            : collect();
    }

    public function updatedSelectedSection()
    {
        // Reset the selected subject whenever the section changes
        $this->reset(['selectedSubject']);

        $user = Auth::user();

        if ($this->selectedSection) {
            if ($user->isAdmin()) {
                $this->subjects = Subject::whereHas('schedules', function ($q) {
                    if ($this->selectedCollege) {
                        $q->where('college_id', $this->selectedCollege);
                    }
                    if ($this->selectedDepartment) {
                        $q->where('department_id', $this->selectedDepartment);
                    }
                    if ($this->selectedSection) {
                        $q->where('section_id', $this->selectedSection);
                    }
                })->get();
            } elseif ($user->isChairperson()) {
                $this->subjects = Subject::whereHas('schedules', function ($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                    if ($this->selectedSection) {
                        $q->where('section_id', $this->selectedSection);
                    }
                })->get();
            } elseif ($user->isInstructor()) {
                $this->subjects = Subject::whereHas('schedules', function ($q) use ($user) {
                    $q->where('instructor_id', $user->id);
                    if ($this->selectedSection) {
                        $q->where('section_id', $this->selectedSection);
                    }
                })->get();
            } elseif ($user->isDean()) {
                $this->subjects = Subject::whereHas('schedules', function ($q) use ($user) {
                    // **Use selectedDepartment instead of user's department_id**
                    $q->where('department_id', $this->selectedDepartment);
                    if ($this->selectedSection) {
                        $q->where('section_id', $this->selectedSection);
                    }
                })->get();
            } elseif ($user->isStudent()) {
                // **For Students:**
                $this->subjects = Subject::whereHas('schedules', function ($q) use ($user) {
                    $q->where('section_id', $user->section_id);
                    if ($this->selectedSection) {
                        $q->where('section_id', $this->selectedSection);
                    }
                })->distinct()->get();
            }
        } else {
            // If no section is selected, reset subjects based on department only
            if ($user->isAdmin()) {
                $this->subjects = Subject::whereHas('schedules', function ($q) {
                    if ($this->selectedCollege) {
                        $q->where('college_id', $this->selectedCollege);
                    }
                    if ($this->selectedDepartment) {
                        $q->where('department_id', $this->selectedDepartment);
                    }
                })->get();
            } elseif ($user->isChairperson()) {
                $this->subjects = Subject::whereHas('schedules', function ($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                })->get();
            } elseif ($user->isInstructor()) {
                $this->subjects = Subject::whereHas('schedules', function ($q) use ($user) {
                    $q->where('instructor_id', $user->id);
                })->get();
            } elseif ($user->isDean()) {
                $this->subjects = Subject::whereHas('schedules', function ($q) use ($user) {
                    // **Use selectedDepartment instead of user's department_id**
                    $q->where('department_id', $this->selectedDepartment);
                })->get();
            } elseif ($user->isStudent()) {
                // **For Students:**
                $this->subjects = Subject::whereHas('schedules', function ($q) use ($user) {
                    $q->where('section_id', $user->section_id);
                })->distinct()->get();
            }
        }
    }

    public function updatedSelectedSubject()
    {
        // Reset pagination when the subject filter changes
        $this->resetPage();
    }

    public function updatedSearch()
    {
        // Reset pagination when the search term changes
        $this->resetPage();
    }

    /**
     * Clear all filters and reset to default state.
     */
    public function clear()
    {
        $user = Auth::user();

        if ($user->isChairperson() || $user->isInstructor()) {
            // Reset only modifiable filters for Chairpersons and Instructors
            $this->reset([
                'search',
                'status',
                'selectedSection',
                'selectedSubject',
            ]);
        } elseif ($user->isStudent()) {
            // **For Students:**
            $this->reset([
                'search',
                'status',
                'selectedSubject',
            ]);
        } else {
            // Reset all filters for Admins and Deans
            $this->reset([
                'search',
                'status',
                'selectedCollege',
                'selectedDepartment',
                'selectedSection',
                'selectedSubject',
            ]);
        }

        // Reset to current month
        $this->selectedMonth = Carbon::now()->format('Y-m');

        // Reload filter options
        $this->initializeFilters();

        // Reset pagination
        $this->resetPage();
    }

    /**
     * Toggle sorting direction or set a new sort field.
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
     * Delete an attendance record with logging.
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

        $attendance->delete();

        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Attendance record deleted successfully');

        // Refresh the table
        $this->refreshAttendanceTable();
    }

    /**
     * Export attendance records in various formats.
     */
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
     * Render the component view with filtered attendance records.
     */
    public function render()
    {
        $query = Attendance::with(['user', 'schedule.subject', 'schedule.section', 'sessions'])
            ->orderBy($this->sortBy, $this->sortDir);

        $user = Auth::user();

        // Apply role-based access control
        if ($user->isAdmin()) {
            // Admin can view all attendance records
            // Apply College filter if selected
            if ($this->selectedCollege) {
                $query->whereHas('user', function ($q) {
                    $q->where('college_id', $this->selectedCollege);
                });
            }

            // Apply Department filter if selected
            if ($this->selectedDepartment) {
                $query->whereHas('user', function ($q) {
                    $q->where('department_id', $this->selectedDepartment);
                });
            }
        } elseif ($user->isDean()) {
            // Dean can view attendances within their college
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('college_id', $user->college_id);
            });

            // Apply Department filter if selected
            if ($this->selectedDepartment) {
                $query->whereHas('user', function ($q) {
                    $q->where('department_id', $this->selectedDepartment);
                });
            }
        } elseif ($user->isChairperson()) {
            // Chairperson can view attendances within their department
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        } elseif ($user->isInstructor()) {
            // Instructor can view attendances related to their schedules
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
            try {
                $parsedMonth = Carbon::parse($this->selectedMonth);
                $query->whereMonth('date', $parsedMonth->month)
                      ->whereYear('date', $parsedMonth->year);
            } catch (\Exception $e) {
                // Handle invalid date format if necessary
            }
        }

        // Paginate the results
        $attendances = $query->paginate($this->perPage);

        // Load filter options based on user role and current selections
        $filterData = $this->getFilterData($user);

        return view('livewire.attendance-table', [
            'attendances' => $attendances,
            'subjects' => $filterData['subjects'],
            'sections' => $filterData['sections'],
            'departments' => $filterData['departments'],
            'colleges' => $filterData['colleges'],
        ]);
    }

    /**
     * Retrieve filter data based on user role.
     */
    protected function getFilterData($user)
    {
        $filterData = [
            'colleges' => collect(),
            'departments' => collect(),
            'sections' => collect(),
            'subjects' => collect(),
        ];

        if ($user->isAdmin()) {
            $filterData['colleges'] = College::all();
            $filterData['departments'] = $this->selectedCollege
                ? Department::where('college_id', $this->selectedCollege)->get()
                : Department::all();
            $filterData['sections'] = $this->selectedDepartment
                ? Section::where('department_id', $this->selectedDepartment)->get()
                : Section::all();
            $filterData['subjects'] = ($this->selectedDepartment || $this->selectedCollege)
                ? Subject::whereHas('schedules', function ($q) use ($user) {
                    if ($this->selectedCollege) {
                        $q->where('college_id', $this->selectedCollege);
                    }
                    if ($this->selectedDepartment) {
                        $q->where('department_id', $this->selectedDepartment);
                    }
                    if ($this->selectedSection) {
                        $q->where('section_id', $this->selectedSection);
                    }
                })->get()
                : Subject::all();
        } elseif ($user->isDean()) {
            $filterData['departments'] = Department::where('college_id', $user->college_id)->get();
            $filterData['sections'] = $this->selectedDepartment
                ? Section::where('department_id', $this->selectedDepartment)->get()
                : Section::whereIn('department_id', $filterData['departments']->pluck('id'))->get();

            // **Use selectedDepartment instead of user's department_id**
            $filterData['subjects'] = $this->selectedDepartment
                ? Subject::whereHas('schedules', function ($q) use ($user) {
                    $q->where('department_id', $this->selectedDepartment);
                    if ($this->selectedSection) {
                        $q->where('section_id', $this->selectedSection);
                    }
                })->get()
                : Subject::whereHas('schedules', function ($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                })->get();
        } elseif ($user->isChairperson()) {
            // Chairpersons have their department set internally
            $filterData['sections'] = Section::where('department_id', $user->department_id)->get();
            $filterData['subjects'] = Subject::whereHas('schedules', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
                if ($this->selectedSection) {
                    $q->where('section_id', $this->selectedSection);
                }
            })->get();
        } elseif ($user->isInstructor()) {
            // Instructors have their department set internally
            $filterData['sections'] = Section::where('department_id', $user->department_id)->get();
            $filterData['subjects'] = Subject::whereHas('schedules', function ($q) use ($user) {
                $q->where('instructor_id', $user->id);
                if ($this->selectedSection) {
                    $q->where('section_id', $this->selectedSection);
                }
            })->get();
        } elseif ($user->isStudent()) {
            // **For Students: Fetch subjects based on section_id**
            $filterData['subjects'] = Subject::whereHas('schedules', function ($q) use ($user) {
                $q->where('section_id', $user->section_id);
            })->distinct()->get();
        }

        return $filterData;
    }

    /**
     * Refresh the attendance table.
     */
    public function refreshAttendanceTable()
    {
        // Reset pagination to refresh data
        $this->resetPage();
    }
}
