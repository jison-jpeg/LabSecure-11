<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\User;
use Livewire\Component;

class ViewStudent extends Component
{
    public $student;
    public $attendanceRecords;
    public $schedules;

    public function mount(User $student)
    {
        $this->student = $student;

        // Load attendance records
        $this->attendanceRecords = Attendance::where('user_id', $student->id)
            ->with('schedule.subject')
            ->get();

        // Load student's schedules
        $this->schedules = Schedule::whereHas('section', function($query) {
            $query->where('section_id', $this->student->section_id);
        })->with('subject', 'instructor')
          ->get();
    }

    public function render()
    {
        return view('livewire.view-student');
    }
}
