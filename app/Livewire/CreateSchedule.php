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
use Livewire\Attributes\On;
use Flasher\Notyf\Prime\NotyfInterface;

class CreateSchedule extends Component
{
    public $formTitle = 'Create Schedule';
    public $editForm = false;
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
    public $conflicts = [];

    public function render()
    {
        return view('livewire.create-schedule', [
            'subjects' => Subject::all(),
            'instructors' => User::where('role_id', 2)->get(),
            'laboratories' => Laboratory::all(),
            'colleges' => College::all(),
            'departments' => Department::all(),
            'sections' => Section::all(),
        ]);
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, [
            'subject_id' => 'required',
            'instructor_id' => 'required',
            'laboratory_id' => 'required',
            'college_id' => 'required',
            'department_id' => 'required',
            'section_id' => 'required',
            'days_of_week' => 'required|array|min:1',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);
    }

    public function save()
    {
        $this->validate([
            'subject_id' => 'required',
            'instructor_id' => 'required',
            'laboratory_id' => 'required',
            'college_id' => 'required',
            'department_id' => 'required',
            'section_id' => 'required',
            'days_of_week' => 'required|array|min:1',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);

        // Check for schedule conflict
        $conflicts = $this->getConflicts($this->instructor_id, $this->section_id, $this->days_of_week, $this->start_time, $this->end_time);

        if ($conflicts->isNotEmpty()) {
            $this->conflicts = $conflicts;
            notyf()
                ->dismissible(true)
                ->position('x', 'right')
                ->position('y', 'top')
                ->error('Oppps! There are conflicting schedules.');
            return;
        }

        Schedule::create([
            'subject_id' => $this->subject_id,
            'instructor_id' => $this->instructor_id,
            'laboratory_id' => $this->laboratory_id,
            'college_id' => $this->college_id,
            'department_id' => $this->department_id,
            'section_id' => $this->section_id,
            'days_of_week' => json_encode($this->days_of_week),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
        ]);

        $this->dispatch('refresh-schedule-table');
        notyf()
            ->dismissible(true)
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Schedule created successfully');
        $this->reset();
    }

    public function getConflicts($instructor_id, $section_id, $days_of_week, $start_time, $end_time, $ignoreScheduleId = null)
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

        return $query->get();
    }

    #[On('reset-modal')]
    public function close()
    {
        $this->resetErrorBag();
        $this->reset();
    }

    #[On('edit-mode')]
    public function edit($id)
    {
        $this->formTitle = 'Edit Schedule';
        $this->editForm = true;
        $this->schedule = Schedule::findOrFail($id);
        $this->subject_id = $this->schedule->subject_id;
        $this->instructor_id = $this->schedule->instructor_id;
        $this->laboratory_id = $this->schedule->laboratory_id;
        $this->college_id = $this->schedule->college_id;
        $this->department_id = $this->schedule->department_id;
        $this->section_id = $this->schedule->section_id;
        $this->days_of_week = json_decode($this->schedule->days_of_week, true);
        $this->start_time = $this->schedule->start_time;
        $this->end_time = $this->schedule->end_time;
    }

    public function update()
    {
        $this->validate([
            'subject_id' => 'required',
            'instructor_id' => 'required',
            'laboratory_id' => 'required',
            'college_id' => 'required',
            'department_id' => 'required',
            'section_id' => 'required',
            'days_of_week' => 'required|array|min:1',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);

        // Check for schedule conflict
        $conflicts = $this->getConflicts($this->instructor_id, $this->section_id, $this->days_of_week, $this->start_time, $this->end_time, $this->schedule->id);

        if ($conflicts->isNotEmpty()) {
            $this->conflicts = $conflicts;
            notyf()
                ->dismissible(true)
                ->position('x', 'right')
                ->position('y', 'top')
                ->error('Oppps! There are conflicting schedules.');
            return;
        }

        $this->schedule->update([
            'subject_id' => $this->subject_id,
            'instructor_id' => $this->instructor_id,
            'laboratory_id' => $this->laboratory_id,
            'college_id' => $this->college_id,
            'department_id' => $this->department_id,
            'section_id' => $this->section_id,
            'days_of_week' => json_encode($this->days_of_week),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
        ]);

        notyf()
            ->dismissible(true)
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Schedule updated successfully');
        $this->dispatch('refresh-schedule-table');
        $this->reset();
    }
}
