<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Schedule;
use Carbon\Carbon;

class UpcomingClass extends Component
{
    public $upcomingClass;
    public $ongoingClass;

    public function mount()
    {
        $this->fetchClasses();
    }

    public function fetchClasses()
    {
        $user = Auth::user();
        $now = Carbon::now();

        // Only handle instructor and student roles
        if ($user->isInstructor()) {
            $this->ongoingClass = Schedule::where('instructor_id', $user->id)
                ->where('start_time', '<=', $now)
                ->where('end_time', '>=', $now)
                ->first();

            $this->upcomingClass = Schedule::where('instructor_id', $user->id)
                ->where('start_time', '>', $now)
                ->orderBy('start_time')
                ->first();
        } elseif ($user->isStudent()) {
            $this->ongoingClass = Schedule::where('section_id', $user->section_id)  // Get schedules for the student's section
                ->where('start_time', '<=', $now)
                ->where('end_time', '>=', $now)
                ->first();

            $this->upcomingClass = Schedule::where('section_id', $user->section_id)  // Get upcoming schedules for the student's section
                ->where('start_time', '>', $now)
                ->whereJsonContains('days_of_week', Carbon::now()->format('l')) // Check if today is a scheduled day
                ->orderBy('start_time')
                ->first();
        }
    }

    public function render()
    {
        return view('livewire.upcoming-class', [
            'ongoingClass' => $this->ongoingClass,
            'upcomingClass' => $this->upcomingClass,
        ]);
    }
}
