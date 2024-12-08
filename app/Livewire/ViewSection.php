<?php

namespace App\Livewire;

use App\Models\Section;
use App\Models\User;
use App\Models\Schedule;
use Livewire\Component;
use Livewire\WithPagination;

class ViewSection extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $section;
    public $activeTab = 'students'; // Default active tab

    public $searchStudents = '';
    public $searchSchedules = '';
    public $perPageStudents = 10;
    public $perPageSchedules = 1;

    public function mount(Section $section)
    {
        $this->section = $section;
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        $students = User::where('section_id', $this->section->id)
            ->when($this->searchStudents, function ($query) {
                $query->where('name', 'like', "%{$this->searchStudents}%")
                    ->orWhere('email', 'like', "%{$this->searchStudents}%");
            })
            ->paginate($this->perPageStudents, ['*'], 'studentsPage');

        $schedules = Schedule::where('section_id', $this->section->id)
            ->when($this->searchSchedules, function ($query) {
                $query->where('name', 'like', "%{$this->searchSchedules}%");
            })
            ->paginate($this->perPageSchedules, ['*'], 'schedulesPage');

        return view('livewire.view-section', [
            'students' => $students,
            'schedules' => $schedules,
        ]);
    }
}
