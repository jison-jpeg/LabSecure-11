<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class ViewSchedule extends Component
{
    use WithPagination;

    public $schedule;
    public $perPage = 10;
    public $search = '';
    public $status = '';
    public $selectedDate;

    public function mount(Schedule $schedule)
    {
        $this->schedule = $schedule;
        $this->selectedDate = Carbon::now()->format('Y-m-d'); // Default to today's date
    }

    public function clear()
    {
        $this->search = '';
        $this->status = '';
        $this->selectedDate = Carbon::now()->format('Y-m-d'); // Reset to today's date
    }

    public function render()
    {
        // Get all students from the section
        $query = User::whereHas('section', function ($q) {
            $q->where('id', $this->schedule->section_id);
        });

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('username', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        // Get attendances for the selected date and filter by status if provided
        $attendancesQuery = Attendance::where('schedule_id', $this->schedule->id)
            ->whereDate('date', $this->selectedDate);

        if (!empty($this->status)) {
            $attendancesQuery->where('status', $this->status);
        }

        $attendances = $attendancesQuery->get()->keyBy('user_id'); // Key attendance by user_id for easy lookup

        // Apply status filter by ensuring only users with matching attendance are displayed
        if (!empty($this->status)) {
            $query->whereIn('id', $attendances->keys()); // Filter users based on attendance status
        }

        // Get paginated users (students)
        $students = $query->paginate($this->perPage);

        return view('livewire.view-schedule', [
            'students' => $students,
            'attendances' => $attendances,
            'schedule' => $this->schedule,
        ]);
    }
}
