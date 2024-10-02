<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\TransactionLog;  // Added TransactionLog model
use App\Models\Subject;  // Added Subject model
use App\Models\Section;  // Added Section model
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
        $query = Attendance::with(['user', 'schedule.subject', 'schedule.section', 'sessions']) // Eager load sessions
            ->orderBy($this->sortBy, $this->sortDir);

        // Check if the authenticated user is not an admin
        if (!Auth::user()->isAdmin()) {
            // Filter attendance records for the authenticated user only
            $query->where('user_id', Auth::id());
        }

        // Apply search filters
        $query->whereHas('user', function ($query) {
            $query->where('first_name', 'like', '%' . $this->search . '%')
                ->orWhere('middle_name', 'like', '%' . $this->search . '%')
                ->orWhere('last_name', 'like', '%' . $this->search . '%')
                ->orWhere('suffix', 'like', '%' . $this->search . '%')
                ->orWhere('username', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%');
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

        // Fetch all subjects and sections for the filter
        $subjects = Subject::all();  // Fetch all subjects
        $sections = Section::all();  // Fetch all sections

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
