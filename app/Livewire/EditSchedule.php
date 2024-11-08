<?php

namespace App\Livewire;

use App\Models\Schedule;
use App\Models\Subject;
use App\Models\User;
use App\Models\College;
use App\Models\Department;
use App\Models\Section;
use App\Models\Laboratory;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EditSchedule extends Component
{
    public $schedule;
    public $subject_id;
    public $instructor_id;
    public $laboratory_id;
    public $college_id;
    public $department_id;
    public $section_id;
    public $days_of_week = [];
    public $start_time;
    public $end_time;
    public Collection $conflicts;

    public function mount(Schedule $schedule)
    {
        $this->schedule = $schedule;
        $this->subject_id = $schedule->subject_id;
        $this->instructor_id = $schedule->instructor_id;
        $this->laboratory_id = $schedule->laboratory_id;
        $this->college_id = $schedule->college_id;
        $this->department_id = $schedule->department_id;
        $this->section_id = $schedule->section_id;
        $this->days_of_week = json_decode($schedule->days_of_week, true);
        $this->start_time = Carbon::parse($schedule->start_time)->format('H:i');
        $this->end_time = Carbon::parse($schedule->end_time)->format('H:i');
        
        // Initialize conflicts as an empty collection
        $this->conflicts = collect();
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, [
            'subject_id'    => 'required|exists:subjects,id',
            'instructor_id' => 'required|exists:users,id',
            'laboratory_id' => 'required|exists:laboratories,id',
            'college_id'    => 'required|exists:colleges,id',
            'department_id' => 'required|exists:departments,id',
            'section_id'    => 'required|exists:sections,id',
            'days_of_week'  => 'required|array|min:1',
            'start_time'    => 'required|date_format:H:i',
            'end_time'      => 'required|date_format:H:i|after:start_time',
        ]);

        if (in_array($propertyName, ['instructor_id', 'section_id', 'days_of_week', 'start_time', 'end_time'])) {
            $this->checkForConflicts();
        }
    }

    public function checkForConflicts()
    {
        // Always store conflicts as a collection
        $this->conflicts = $this->getConflicts(
            $this->instructor_id,
            $this->section_id,
            $this->days_of_week,
            $this->start_time,
            $this->end_time,
            $this->schedule->id
        );
    }

    public function update()
    {
        $this->conflicts = collect(); // Reset conflicts to an empty collection

        $this->validate([
            'subject_id'    => 'required|exists:subjects,id',
            'instructor_id' => 'required|exists:users,id',
            'laboratory_id' => 'required|exists:laboratories,id',
            'college_id'    => 'required|exists:colleges,id',
            'department_id' => 'required|exists:departments,id',
            'section_id'    => 'required|exists:sections,id',
            'days_of_week'  => 'required|array|min:1',
            'start_time'    => 'required|date_format:H:i',
            'end_time'      => 'required|date_format:H:i|after:start_time',
        ]);

        $existingSchedule = Schedule::where('subject_id', $this->subject_id)
            ->where('section_id', $this->section_id)
            ->where('department_id', $this->department_id)
            ->where('id', '!=', $this->schedule->id)
            ->first();

        if ($existingSchedule) {
            $this->addError('subject_id', 'This subject already exists for the selected section and department.');
            return;
        }

        $this->checkForConflicts();
        if ($this->conflicts->isNotEmpty()) {
            return;
        }

        try {
            $this->schedule->update([
                'subject_id'    => $this->subject_id,
                'instructor_id' => $this->instructor_id,
                'laboratory_id' => $this->laboratory_id,
                'college_id'    => $this->college_id,
                'department_id' => $this->department_id,
                'section_id'    => $this->section_id,
                'days_of_week'  => json_encode($this->days_of_week),
                'start_time'    => $this->start_time,
                'end_time'      => $this->end_time,
            ]);

            notyf()->success('Schedule updated successfully');
            $this->dispatch('close-modal');

        } catch (\Exception $e) {
            notyf()->error('Something went wrong, please try again later');
        }
    }

    protected function getConflicts($instructor_id, $section_id, $days_of_week, $start_time, $end_time, $ignoreScheduleId = null)
    {
        $query = Schedule::where(function ($query) use ($instructor_id, $section_id) {
            $query->where('instructor_id', $instructor_id)
                  ->orWhere('section_id', $section_id);
        })
        ->where(function ($query) use ($days_of_week) {
            foreach ($days_of_week as $day) {
                $query->orWhereJsonContains('days_of_week', $day);
            }
        })
        ->where(function ($query) use ($start_time, $end_time) {
            $query->where(function ($query) use ($start_time, $end_time) {
                $query->where('start_time', '<', $end_time)
                      ->where('end_time', '>', $start_time);
            });
        });

        if ($ignoreScheduleId) {
            $query->where('id', '!=', $ignoreScheduleId);
        }

        return $query->get(); // This will return a collection
    }

    public function render()
    {
        return view('livewire.edit-schedule', [
            'subjects'      => Subject::all(),
            'instructors'   => User::where('role_id', 2)->get(),
            'laboratories'  => Laboratory::all(),
            'colleges'      => College::all(),
            'departments'   => Department::all(),
            'sections'      => Section::all(),
        ]);
    }
}
