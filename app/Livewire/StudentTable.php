<?php

namespace App\Livewire;

use App\Exports\StudentExport;
use App\Imports\StudentImport;
use App\Models\User;
use App\Models\College;
use App\Models\Department;
use App\Models\Schedule;
use App\Models\Section;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class StudentTable extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    public $title = 'Manage Students';
    public $event = 'create-student';
    public $studentFile;
    public $importErrors = [];
    public $importSummary = '';

    // Filter Properties
    public $search = '';
    public $sortBy = 'created_at';
    public $sortDir = 'DESC';
    public $perPage = 10;

    public $college = '';
    public $department = '';
    public $scheduleCode = '';
    public $section = '';
    public $yearLevel = ''; // New Filter Property

    // To store the filtered departments dynamically
    public $filteredDepartments = [];

    // To store available sections based on department and year level selection
    public $availableSections = [];

    // To store available year levels (optional)
    public $availableYearLevels = [];

    public function mount()
    {
        // Initialize filteredDepartments based on user role
        $this->initializeFilteredDepartments();

        // Initialize availableSections based on current filters and role
        $this->updateAvailableSections();

        // Initialize availableYearLevels
        $this->availableYearLevels = Section::select('year_level')->distinct()->pluck('year_level');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCollege()
    {
        $this->resetPage();
        $this->department = ''; // Reset department when college changes
        $this->updateAvailableSections(); // Update Sections based on new Department
    }

    public function updatedDepartment()
    {
        $this->resetPage();
        $this->section = ''; // Reset Section when Department changes
        $this->updateAvailableSections(); // Update Sections based on new Department
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
        $this->scheduleCode = '';
        $this->section = '';
        $this->yearLevel = ''; // Reset Year Level
        $this->resetPage();
        $this->initializeFilteredDepartments();
        $this->updateAvailableSections();
    }

    public function setSortBy($sortByField)
    {
        $sortableFields = ['username', 'email', 'section.name', 'year_level', 'first_name', 'middle_name', 'last_name', 'suffix', 'college.name', 'department.name', 'created_at']; // Add other sortable fields as needed

        if (!in_array($sortByField, $sortableFields)) {
            return;
        }

        if ($this->sortBy === $sortByField) {
            $this->sortDir = ($this->sortDir === "ASC") ? 'DESC' : "ASC";
            return;
        }

        $this->sortBy = $sortByField;
        $this->sortDir = 'DESC';
    }

    public function delete(User $student)
    {
        $student->delete();
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Student deleted successfully');
    }

    public function importStudents()
    {
        $this->validate([
            'studentFile' => 'required|file|mimes:csv,xlsx',
        ]);

        $import = new StudentImport();

        try {
            Excel::import($import, $this->studentFile->getRealPath());

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
                $this->importSummary = "$successCount out of $totalCount students imported successfully. $skippedCount students were skipped: $skippedDetails.";
                $this->importErrors = [];

            } else {
                // Full success: Show success message in Notyf if all records imported
                $message = "$totalCount students imported successfully.";
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

        $this->reset('studentFile');
    }

    public function exportAs($format)
{
    $timestamp = now()->format('Y_m_d_H_i_s'); // Include date and time in filenames
    $fileName = "Student_Export_{$timestamp}";

    switch ($format) {
        case 'csv':
            return Excel::download(new StudentExport($this->college, $this->department, $this->yearLevel, $this->section), "{$fileName}.csv");

        case 'excel':
            return Excel::download(new StudentExport($this->college, $this->department, $this->yearLevel, $this->section), "{$fileName}.xlsx");

        case 'pdf':
            $students = User::whereHas('role', function ($query) {
                $query->where('name', 'student'); // Use role name for students
            })
            ->when($this->college, function ($query) {
                $query->where('college_id', $this->college);
            })
            ->when($this->department, function ($query) {
                $query->where('department_id', $this->department);
            })
            ->when($this->yearLevel, function ($query) {
                $query->whereHas('section', function ($q) {
                    $q->where('year_level', $this->yearLevel);
                });
            })
            ->when($this->section, function ($query) {
                $query->where('section_id', $this->section);
            })
            ->with(['college', 'department', 'section', 'role'])
            ->get();

            $pdf = Pdf::loadView('exports.student_report', [
                'students' => $students,
                'collegeFilter' => College::find($this->college),
                'departmentFilter' => Department::find($this->department),
                'sectionFilter' => Section::find($this->section),
                'yearLevelFilter' => $this->yearLevel,
                'generatedBy' => Auth::user(),
            ])->setPaper('a4', 'portrait');

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, "{$fileName}.pdf");

        default:
            notyf()->error('Unsupported export format.');
            break;
    }
}


    public function updatedStudentFile()
    {
        $this->reset(['importErrors', 'importSummary']);
    }

    /**
     * Initialize filteredDepartments based on user role and selected college
     */
    private function initializeFilteredDepartments()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            if ($this->college !== '') {
                $this->filteredDepartments = Department::where('college_id', $this->college)->get();
            } else {
                $this->filteredDepartments = Department::all();
            }
        } elseif ($user->isDean()) {
            // For Dean, departments are within their college
            $this->filteredDepartments = Department::where('college_id', $user->college_id)->get();
        } else {
            // For Chairperson, Instructor, and other roles, no department filter is needed
            $this->filteredDepartments = collect();
        }
    }

    /**
     * Get the sections managed by the instructor
     */
    private function getInstructorSections()
    {
        $user = Auth::user();

        if ($user->isInstructor()) {
            return Section::whereHas('schedules', function ($q) use ($user) {
                $q->where('instructor_id', $user->id);
            })->pluck('id')->toArray();
        }

        return [];
    }

    /**
     * Update the availableSections based on the selected department, year level, and user role
     */
    private function updateAvailableSections()
    {
        $user = Auth::user();

        // If no Year Level is selected, clear availableSections and exit
        if (empty($this->yearLevel)) {
            $this->availableSections = collect();
            return;
        }

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

        // Apply Year Level filter
        if ($this->yearLevel !== '') {
            $query->where('year_level', $this->yearLevel);
        }

        $this->availableSections = $query->get();
    }

    public function render()
    {
        $user = Auth::user();

        // Initialize the query with only students
        $query = User::query()->with(['college', 'department', 'section'])
            ->whereHas('role', function ($q) {
                $q->where('name', 'student');
            });

        // Apply role-based filters
        if ($user->isAdmin()) {
            // Admin sees all students

            // Apply College filter if selected
            if ($this->college !== '') {
                $query->where('college_id', $this->college);
            }

            // Apply Department filter if selected
            if ($this->department !== '') {
                $query->where('department_id', $this->department);
            }
        } elseif ($user->isDean()) {
            // Dean sees all students in their college
            $query->where('college_id', $user->college_id);

            // Apply Department filter if selected
            if ($this->department !== '') {
                $query->where('department_id', $this->department);
            }
        } elseif ($user->isChairperson()) {
            // Chairperson sees students in their department only
            $query->where('department_id', $user->department_id);
        } elseif ($user->isInstructor()) {
            // Instructor sees only students in their managed sections
            $instructorSections = $this->getInstructorSections();
            $query->whereIn('section_id', $instructorSections);
        }

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('middle_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('suffix', 'like', '%' . $this->search . '%')
                    ->orWhere('username', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        // Apply additional filters based on user role
        if ($this->scheduleCode) {
            $query->whereHas('section.schedules', function ($q) {
                $q->where('schedule_code', $this->scheduleCode);
            });
        }

        if ($this->section) {
            $query->where('section_id', $this->section);
        }

        // Apply Year Level filter
        if ($this->yearLevel) {
            $query->whereHas('section', function ($q) {
                $q->where('year_level', $this->yearLevel);
            });
        }

        // Apply sorting
        if ($this->sortBy === 'year_level') {
            $query->join('sections', 'users.section_id', '=', 'sections.id')
                  ->orderBy('sections.year_level', $this->sortDir)
                  ->select('users.*'); // Ensure you select users.* to avoid column conflicts
        } else {
            $query->orderBy($this->sortBy, $this->sortDir);
        }

        // Get paginated students
        $students = $query->paginate($this->perPage);

        // Determine which filters to show based on role
        $colleges = $user->isAdmin() ? College::all() : collect([]);
        $departments = ($user->isAdmin() || $user->isDean()) ? $this->filteredDepartments : collect([]);
        $schedules = ($user->isAdmin() || $user->isDean() || $user->isChairperson() || $user->isInstructor()) 
            ? ($user->isInstructor() ? Schedule::where('instructor_id', $user->id)->get() : Schedule::all()) 
            : collect([]);
        $sections = $this->availableSections; // Use the dynamically updated availableSections

        return view('livewire.student-table', [
            'users' => $students,
            'colleges' => $colleges,
            'departments' => $departments,
            'schedules' => $schedules,
            'sections' => $sections, // Pass availableSections as 'sections'
            'availableYearLevels' => $this->availableYearLevels, // Pass availableYearLevels
        ]);
    }
}
