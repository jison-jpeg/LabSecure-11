<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Schedule;
use Livewire\WithPagination;

class ViewFaculty extends Component
{
    use WithPagination;

    public $faculty;
    public $perPage = 10;
    public $search = '';
    public $selectedScheduleCode = '';
    public $selectedSection = '';
    public $scheduleCodes = [];
    public $sections = [];

    public function mount(User $faculty)
    {
        $this->faculty = $faculty;

        // Fetch unique schedule codes and sections for this faculty's schedules
        $this->scheduleCodes = Schedule::where('instructor_id', $faculty->id)
            ->pluck('schedule_code')
            ->unique()
            ->toArray();

        $this->sections = Schedule::where('instructor_id', $faculty->id)
            ->with('section')
            ->get()
            ->pluck('section.name')
            ->unique()
            ->toArray();
    }

    public function clear()
    {
        $this->search = '';
        $this->selectedScheduleCode = '';
        $this->selectedSection = '';
    }

    public function render()
    {
        // Get schedules handled by the faculty member
        $schedules = Schedule::where('instructor_id', $this->faculty->id)
            ->when($this->search, function ($query) {
                // Allow partial search for subject, schedule_code, and section name
                $query->whereHas('subject', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })
                ->orWhere('schedule_code', 'like', '%' . $this->search . '%')
                ->orWhereHas('section', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->selectedScheduleCode, function ($query) {
                $query->where('schedule_code', $this->selectedScheduleCode);
            })
            ->when($this->selectedSection, function ($query) {
                $query->whereHas('section', function ($q) {
                    $q->where('name', $this->selectedSection);
                });
            })
            ->paginate($this->perPage);

        return view('livewire.view-faculty', [
            'schedules' => $schedules,
        ]);
    }
}
