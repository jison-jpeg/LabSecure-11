<?php

namespace App\Livewire;

use App\Exports\ScheduleExport;
use App\Imports\ScheduleImport;
use App\Models\Schedule;
use App\Models\College;
use App\Models\Department;
use App\Models\Section;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ScheduleTable extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    public $user;
    public $userId;

    public $scheduleFile;
    public $importErrors = [];
    public $importSummary = '';
    public $title = 'Manage Schedules';
    public $event = 'create-schedule';

    // Filter Properties
    public $search = '';
    public $sortBy = 'created_at';
    public $sortDir = 'DESC';
    public $perPage = 10;

    public $college = '';
    public $department = '';
    public $yearLevel = ''; // New Filter Property
    public $section = ''; // Section filter

    // To store the filtered departments dynamically
    public $filteredDepartments = [];

    // To store available sections based on department and year level selection
    public $availableSections = [];

    // To store available year levels
    public $availableYearLevels = [];

    // To store the hidden filters based on user role
    public $hideFilters = [];

    public function mount($userId = null)
{
    $this->userId = $userId; // Assign the userId from the parent component
    $this->user = Auth::user();

    if ($this->userId) {
        $this->hideFilters = ['college', 'department', 'yearLevel'];
    }

    // Initialize filteredDepartments based on user role
    $this->initializeFilteredDepartments();

    // Populate availableYearLevels based on the selected department
    $this->updateAvailableYearLevels();

    // Initialize availableSections based on current filters and role
    $this->updateAvailableSections();
}

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCollege()
    {
        $this->resetPage();
        $this->department = ''; // Reset department when college changes
        $this->updateFilteredDepartments();
        $this->updateAvailableYearLevels(); // Update Year Levels based on new Department
        $this->updateAvailableSections(); // Update Sections based on new Department and Year Level
    }

    public function updatedDepartment()
    {
        $this->resetPage();
        $this->yearLevel = ''; // Reset Year Level when Department changes
        $this->section = ''; // Reset Section when Department changes
        $this->updateAvailableYearLevels(); // Update Year Levels based on new Department
        $this->updateAvailableSections(); // Update Sections based on new Department and Year Level
    }

    public function updatedYearLevel()
    {
        $this->resetPage();
        $this->section = ''; // Reset Section when Year Level changes
        $this->updateAvailableSections(); // Update Sections based on new Year Level
    }

    public function clear()
    {
        $this->search = '';
        $this->college = '';
        $this->department = '';
        $this->yearLevel = ''; // Reset Year Level
        $this->section = ''; // Reset Section
        $this->sortBy = 'created_at';
        $this->sortDir = 'DESC';
        $this->perPage = 10;
        $this->resetPage();
        $this->initializeFilteredDepartments();
        $this->updateAvailableYearLevels(); // Update Year Levels based on reset Department
        $this->updateAvailableSections(); // Update Sections based on reset Department and Year Level
    }

    public function setSortBy($sortByField)
    {
        $sortableFields = [
            'schedule_code',
            'section.name',
            'section.year_level', // Added Year Level as sortable field
            'subject.code',
            'subject.name',
            'instructor.full_name',
            'college.name',
            'department.name',
            'laboratory.name',
            'days_of_week',
            'start_time',
            'end_time',
            'created_at',
        ]; // Add other sortable fields as needed

        if (!in_array($sortByField, $sortableFields)) {
            return;
        }

        if ($this->sortBy === $sortByField) {
            $this->sortDir = ($this->sortDir == "ASC") ? 'DESC' : "ASC";
            return;
        }

        $this->sortBy = $sortByField;
        $this->sortDir = 'DESC';
    }

    public function delete(Schedule $schedule)
    {
        $schedule->delete();
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Schedule deleted successfully');
    }

    public function importSchedules()
    {
        $this->validate([
            'scheduleFile' => 'required|file|mimes:csv,xlsx',
        ]);

        $import = new ScheduleImport();

        try {
            Excel::import($import, $this->scheduleFile->getRealPath());

            // Track success and skipped counts
            $successCount = $import->successfulImports;
            $skippedCount = count($import->skipped);
            $totalCount = $successCount + $skippedCount;

            if (count($import->failures) > 0) {
                // Collect row-level validation errors to display in modal
                $this->importErrors = [];
                foreach ($import->failures as $failure) {
                    $this->importErrors[] = "Row {$failure->row()}: " . implode(", ", $failure->errors());
                }
                return;
            } elseif ($skippedCount > 0) {
                // Partial success: Display summary with skipped details in modal
                $skippedDetails = implode(", ", $import->skipped);
                $this->importSummary = "$successCount out of $totalCount schedules imported successfully. $skippedCount schedules were skipped: $skippedDetails.";
                $this->importErrors = [];
            } else {
                // Full success: Show success message in Notyf if all records imported
                $message = "$totalCount schedules imported successfully.";
                notyf()
                    ->position('x', 'right')
                    ->position('y', 'top')
                    ->success($message);

                // Close modal and reset fields
                $this->dispatch('close-import-modal');
                $this->reset(['importErrors', 'importSummary']);
            }
        } catch (\Exception $e) {
            // Handle unexpected errors
            $this->importErrors = ['Error: ' . $e->getMessage()];
            $this->importSummary = '';

            notyf()
                ->position('x', 'right')
                ->position('y', 'top')
                ->error('An unexpected error occurred during import.');
        }

        $this->reset('scheduleFile');
    }

    public function updatedScheduleFile()
    {
        $this->reset(['importErrors', 'importSummary']);
    }

    public function exportAs($format)
{
    $timestamp = now()->format('Y_m_d_H_i_s'); // Include date and time in filenames
    $fileName = "Schedule_Export_{$timestamp}";

    // Retrieve filtered colleges, departments, and schedules
    $colleges = College::with(['departments' => function ($query) {
        $query->when($this->department, function ($query) {
            $query->where('id', $this->department);
        })->with(['schedules' => function ($query) {
            // Filter schedules for a specific department or college
            $query->when($this->department, function ($query) {
                $query->where('department_id', $this->department);
            })
                ->when($this->college, function ($query) {
                    $query->where('college_id', $this->college);
                })
                ->when($this->yearLevel, function ($query) {
                    $query->whereHas('section', function ($q) {
                        $q->where('year_level', $this->yearLevel);
                    });
                })
                ->when($this->section, function ($query) {
                    $query->where('section_id', $this->section);
                });

            // Role-based filtering
            $user = Auth::user();
            if ($user->isDean()) {
                $query->where('college_id', $user->college_id); // Filter schedules by the Dean's college
            } elseif ($user->isChairperson()) {
                $query->where('department_id', $user->department_id); // Filter schedules by the Chairperson's department
            } elseif ($user->isInstructor()) {
                $query->where('instructor_id', $user->id); // Filter schedules by the Instructor's ID
            } elseif ($user->isStudent()) {
                $query->where('section_id', $user->section_id); // Filter schedules by the Student's section
            }
        }]);
    }])
        ->when($this->college, function ($query) {
            $query->where('id', $this->college);
        })
        ->when($this->department && !$this->college, function ($query) {
            // Ensure that the query includes the college related to the specified department
            $query->whereHas('departments', function ($subQuery) {
                $subQuery->where('id', $this->department);
            });
        })
        ->get();

    $selectedCollegeName = $this->college ? College::find($this->college)->name : null;
    $isSpecificExport = $this->college || $this->department;

    switch ($format) {
        case 'csv':
            return Excel::download(new ScheduleExport($colleges), "{$fileName}.csv");
        case 'excel':
            return Excel::download(new ScheduleExport($colleges), "{$fileName}.xlsx");
            case 'pdf':
                $user = Auth::user(); // Get authenticated user
                $schedules = Schedule::query()
                    ->when($user->isInstructor(), fn($q) => $q->where('instructor_id', $user->id))
                    ->when($user->isStudent(), fn($q) => $q->where('section_id', $user->section_id))
                    ->get();
            
                $pdf = Pdf::loadView('exports.schedule_report', [
                    'user' => $user,
                    'schedules' => $schedules,
                    'colleges' => !$user->isStudent() && !$user->isInstructor() ? $colleges : [],
                    'selectedMonth' => now(),
                    'generatedBy' => $user->full_name,
                ])->setPaper('a4', 'portrait');
            
                return response()->streamDownload(function () use ($pdf) {
                    echo $pdf->output();
                }, "{$fileName}.pdf");
            

        default:
            notyf()->error('Unsupported export format.');
            break;
    }
}

    /**
     * Get export filters based on current filters
     */
    private function getExportFilters()
    {
        return [
            'search' => $this->search,
            'college' => $this->college,
            'department' => $this->department,
            'yearLevel' => $this->yearLevel,
            'section' => $this->section,
            'sortBy' => $this->sortBy,
            'sortDir' => $this->sortDir,
        ];
    }

    /**
     * Initialize filteredDepartments based on user role and selected college
     */
    private function initializeFilteredDepartments()
    {
        $user = $this->user;

        if ($user->isAdmin()) {
            if ($this->college !== '') {
                $this->filteredDepartments = Department::where('college_id', $this->college)
                    ->orderBy('name')
                    ->get();
            } else {
                $this->filteredDepartments = Department::orderBy('name')->get();
            }
        } elseif ($user->isDean()) {
            // For Dean, departments are within their college
            $this->filteredDepartments = Department::where('college_id', $user->college_id)
                ->orderBy('name')
                ->get();
        } else {
            // For Chairperson and other roles, no department filter is needed
            $this->filteredDepartments = collect();
        }
    }

    /**
     * Update filteredDepartments when college changes
     */
    private function updateFilteredDepartments()
    {
        $user = $this->user;

        if ($user->isAdmin()) {
            if ($this->college !== '') {
                $this->filteredDepartments = Department::where('college_id', $this->college)
                    ->orderBy('name')
                    ->get();
            } else {
                $this->filteredDepartments = Department::orderBy('name')->get();
            }
        } elseif ($user->isDean()) {
            // For Dean, departments are within their college
            $this->filteredDepartments = Department::where('college_id', $user->college_id)
                ->orderBy('name')
                ->get();
        } else {
            // For Chairperson and other roles, no department filter is needed
            $this->filteredDepartments = collect();
        }
    }

    /**
     * Update availableYearLevels based on the selected department
     */
    private function updateAvailableYearLevels()
    {
        if ($this->department !== '') {
            // Fetch distinct Year Levels for the selected Department
            $this->availableYearLevels = Section::where('department_id', $this->department)
                ->select('year_level')
                ->distinct()
                ->orderBy('year_level')
                ->pluck('year_level');
        } else {
            // Fetch all distinct Year Levels across all Departments
            $this->availableYearLevels = Section::select('year_level')
                ->distinct()
                ->orderBy('year_level')
                ->pluck('year_level');
        }
    }

    /**
     * Update the availableSections based on the selected department, year level, and user role
     */
    private function updateAvailableSections()
    {
        $user = $this->user;

        $query = Section::query();

        if ($user->isAdmin()) {
            if ($this->department !== '') {
                $query->where('department_id', $this->department);
            }
        } elseif ($user->isDean()) {
            $query->whereHas('department', function ($q) use ($user) {
                $q->where('college_id', $user->college_id);
            });

            if ($this->department !== '') {
                $query->where('department_id', $this->department);
            }
        } elseif ($user->isChairperson()) {
            if ($this->department !== '') {
                if ($this->department == $user->department_id) {
                    $query->where('department_id', $this->department);
                } else {
                    // If Department does not match, return no sections
                    $query->whereRaw('1 = 0');
                }
            } else {
                $query->where('department_id', $user->department_id);
            }
        } elseif ($user->isInstructor()) {
            $instructorSections = $this->getInstructorSections();

            if ($this->department !== '') {
                $query->whereIn('id', $instructorSections)
                    ->where('department_id', $this->department);
            } else {
                $query->whereIn('id', $instructorSections);
            }
        } else {
            // For other roles or unauthenticated users, show no Sections
            $query->whereRaw('1 = 0');
        }

        // Apply Year Level filter if selected
        if ($this->yearLevel !== '') {
            $query->where('year_level', $this->yearLevel);
        }

        $this->availableSections = $query->orderBy('name')->get();
    }

    /**
     * Get the sections managed by the instructor
     */
    private function getInstructorSections()
    {
        $user = $this->user;

        if ($user->isInstructor()) {
            return Section::whereHas('schedules', function ($q) use ($user) {
                $q->where('instructor_id', $user->id);
            })->pluck('id')->toArray();
        }

        return [];
    }

    public function render()
{
    $user = $this->user;

    // Initialize the query
    $query = Schedule::query()->with(['college', 'department', 'section', 'instructor', 'subject', 'laboratory']);

    // Role-based filtering
    if ($user->isAdmin()) {
        // Admin sees all schedules

        // Apply College filter if selected
        if ($this->college !== '') {
            $query->where('college_id', $this->college);
        }

        // Apply Department filter if selected
        if ($this->department !== '') {
            $query->where('department_id', $this->department);
        }
    } elseif ($user->isDean()) {
        // Dean sees schedules within their college
        $query->where('college_id', $user->college_id);

        // Apply Department filter if selected
        if ($this->department !== '') {
            $query->where('department_id', $this->department);
        }
    } elseif ($user->isChairperson()) {
        // Chairperson sees schedules within their department
        $query->where('department_id', $user->department_id);
    } elseif ($user->isInstructor()) {
        // Instructor sees schedules assigned to them
        $query->where('instructor_id', $user->id);
    } elseif ($user->isStudent()) {
        // Student sees only their own schedules
        if ($user->section_id) {
            $query->where('section_id', $user->section_id);
        } else {
            $query->whereNull('id'); // Return no results if student has no section
        }
    }

    // Apply `userId` filter (for View Student page)
    if ($this->userId) {
        $query->whereHas('section.students', function ($q) {
            $q->where('id', $this->userId); // Filter schedules for the specific student's section
        });
    }

    // Apply search filter
    if ($this->search) {
        $query->where(function ($q) {
            $q->where('schedule_code', 'like', '%' . $this->search . '%')
                ->orWhereHas('subject', function ($q2) {
                    $q2->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('code', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('instructor', function ($q2) {
                    $q2->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('section', function ($q2) {
                    $q2->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('year_level', 'like', '%' . $this->search . '%');
                });
        });
    }

    // Apply sorting
    if ($this->sortBy === 'section.year_level') {
        $query->join('sections', 'schedules.section_id', '=', 'sections.id')
            ->orderBy('sections.year_level', $this->sortDir)
            ->select('schedules.*'); // Ensure proper column selection
    } else {
        $query->orderBy($this->sortBy, $this->sortDir);
    }

    // Apply pagination
    $schedules = $query->paginate($this->perPage);

    // Fetch filter options based on role
    $colleges = $user->isAdmin() ? College::orderBy('name')->get() : collect([]);
    $departments = ($user->isAdmin() || $user->isDean()) ? $this->filteredDepartments : collect([]);

    return view('livewire.schedule-table', [
        'schedules' => $schedules,
        'colleges' => $colleges,
        'departments' => $departments,
        'availableYearLevels' => $this->availableYearLevels,
        'sections' => $this->availableSections,
    ]);
}

}
