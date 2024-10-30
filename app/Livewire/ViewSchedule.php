<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ViewSchedule extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $schedule;
    public $perPage = 10;
    public $search = '';
    public $status = ''; // 'present', 'absent'
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
        $this->resetPage();
    }

    public function render()
    {
        // Base query: select students in the schedule's section
        $query = User::select('users.*', DB::raw('COALESCE(attendances.status, "absent") as attendance_status'))
            ->leftJoin('attendances', function ($join) {
                $join->on('users.id', '=', 'attendances.user_id')
                     ->where('attendances.schedule_id', '=', $this->schedule->id)
                     ->whereDate('attendances.date', '=', $this->selectedDate);
            })
            ->where('users.section_id', $this->schedule->section_id)
            ->whereHas('role', function($q){
                $q->where('name', 'student');
            });

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('users.first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('users.last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('users.username', 'like', '%' . $this->search . '%')
                  ->orWhere('users.email', 'like', '%' . $this->search . '%');
            });
        }

        // Apply status filter
        if (!empty($this->status)) {
            if ($this->status === 'absent') {
                // Include students with 'absent' status or no attendance record
                $query->where(function ($q) {
                    $q->where('attendances.status', 'absent')
                      ->orWhereNull('attendances.status');
                });
            } else {
                // For other statuses like 'present', filter accordingly
                $query->where('attendances.status', $this->status);
            }
        }

        // Get paginated students with attendance status
        $students = $query->orderBy('users.last_name', 'asc')
                          ->paginate($this->perPage);

        return view('livewire.view-schedule', [
            'students' => $students,
            'schedule' => $this->schedule,
        ]);
    }
}
