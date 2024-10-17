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
        $currentDay = Carbon::now()->format('l'); // Get the current day of the week (e.g., 'Monday')

        // Only handle instructor and student roles
        if ($user->isInstructor()) {
            // Ongoing class based on today's schedules
            $this->ongoingClass = Schedule::where('instructor_id', $user->id)
                ->whereJsonContains('days_of_week', $currentDay) // Filter by the current day
                ->where('start_time', '<=', $now)
                ->where('end_time', '>=', $now)
                ->first();

            // Upcoming class based on today's schedules
            $this->upcomingClass = Schedule::where('instructor_id', $user->id)
                ->whereJsonContains('days_of_week', $currentDay) // Filter by the current day
                ->where('start_time', '>', $now)
                ->orderBy('start_time')
                ->first();
        } elseif ($user->isStudent()) {
            // Ongoing class for students based on today's schedules
            $this->ongoingClass = Schedule::where('section_id', $user->section_id) // Get schedules for the student's section
                ->whereJsonContains('days_of_week', $currentDay) // Filter by the current day
                ->where('start_time', '<=', $now)
                ->where('end_time', '>=', $now)
                ->first();

            // Upcoming class for students based on today's schedules
            $this->upcomingClass = Schedule::where('section_id', $user->section_id) // Get upcoming schedules for the student's section
                ->whereJsonContains('days_of_week', $currentDay) // Filter by the current day
                ->where('start_time', '>', $now)
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
