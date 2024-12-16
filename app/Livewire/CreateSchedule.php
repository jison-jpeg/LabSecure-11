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
use Illuminate\Support\Facades\Auth;


class CreateSchedule extends Component
{
    public $formTitle = 'Create Schedule';
    public $editForm = false;
    public $lockError = null;
    public $schedule;

    // Form Fields
    public $subject_id;
    public $instructor_id;
    public $laboratory_id;
    public $college_id;
    public $department_id;
    public $year_level; // Year Level is used to filter sections
    public $section_id;
    public $days_of_week = [];
    public $start_time;
    public $end_time;
    public $conflicts = [];

    // Listeners
    protected $listeners = [
        'refresh-schedule-table' => '$refresh',
    ];

    /**
     * Render the Livewire component view with dynamic data.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Fetch departments based on selected college
        $departments = $this->college_id
            ? Department::where('college_id', $this->college_id)->get()
            : Department::all(); // Show all departments if no college is selected

        // Fetch unique year levels based on selected department
        $yearLevels = $this->department_id
            ? Section::where('department_id', $this->department_id)
            ->pluck('year_level')
            ->unique()
            ->sort()
            : collect(); // Empty list if no department is selected

        // Fetch sections based on selected department and year level
        $sections = ($this->department_id && $this->year_level)
            ? Section::where('department_id', $this->department_id)
            ->where('year_level', $this->year_level)
            ->get()
            : collect(); // Empty list if department or year level not selected

        return view('livewire.create-schedule', [
            'subjects' => Subject::all(),
            'instructors' => User::where('role_id', 2)->get(), // Assuming role_id 2 is for instructors
            'laboratories' => Laboratory::all(),
            'colleges' => College::all(),
            'departments' => $departments,
            'year_levels' => $yearLevels,
            'sections' => $sections,
        ]);
    }

    /**
     * Validate individual properties when they are updated.
     *
     * @param string $propertyName
     * @return void
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, [
            'subject_id' => 'required',
            'instructor_id' => 'required',
            'laboratory_id' => 'required',
            'college_id' => 'required',
            'department_id' => 'required',
            'year_level' => 'required|integer|min:1',
            'section_id' => 'required',
            'days_of_week' => 'required|array|min:1',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);
    }

    /**
     * Save a new schedule.
     *
     * @return void
     */
    public function save()
    {
        $this->validate([
            'subject_id' => 'required',
            'instructor_id' => 'required',
            'laboratory_id' => 'required',
            'college_id' => 'required',
            'department_id' => 'required',
            'year_level' => 'required|integer|min:1',
            'section_id' => 'required',
            'days_of_week' => 'required|array|min:1',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);

        // Check for subject uniqueness within the same department and section
        $existingSchedule = Schedule::where('subject_id', $this->subject_id)
            ->where('section_id', $this->section_id)
            ->where('department_id', $this->department_id)
            ->first();

        if ($existingSchedule) {
            notyf()
                ->dismissible(true)
                ->position('x', 'right')
                ->position('y', 'top')
                ->error('This subject already exists for the selected section and department.');
            return;
        }

        // Check for schedule conflicts
        $conflicts = $this->getConflicts(
            $this->instructor_id,
            $this->section_id,
            $this->days_of_week,
            $this->start_time,
            $this->end_time
        );

        if ($conflicts->isNotEmpty()) {
            $this->conflicts = $conflicts;
            notyf()
                ->dismissible(true)
                ->position('x', 'right')
                ->position('y', 'top')
                ->error('Oops! There are conflicting schedules.');
            return;
        }

        // Generate the schedule code
        $schedule_code = $this->generateScheduleCode();

        // Create the schedule without saving 'year_level'
        Schedule::create([
            'subject_id' => $this->subject_id,
            'instructor_id' => $this->instructor_id,
            'laboratory_id' => $this->laboratory_id,
            'college_id' => $this->college_id,
            'department_id' => $this->department_id,
            'section_id' => $this->section_id,
            'schedule_code' => $schedule_code,
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
        $this->resetForm();
    }

    /**
     * Update an existing schedule.
     *
     * @return void
     */
    public function update()
    {
        // Check if the schedule is locked by another user
        if ($this->schedule->isLocked() && !$this->schedule->isLockedBy(Auth::id())) {
            $lockDetails = $this->schedule->lockDetails();
            $lockedByName = $lockDetails['user'] ? $lockDetails['user']->full_name : 'another user';
            $timeAgo = $lockDetails['timeAgo'];

            notyf()
                ->position('x', 'right')
                ->position('y', 'top')
                ->error("This schedule is currently being edited by {$lockedByName} ({$timeAgo}). Please try again later.");
            return;
        }

        $this->validate([
            'subject_id' => 'required',
            'instructor_id' => 'required',
            'laboratory_id' => 'required',
            'college_id' => 'required',
            'department_id' => 'required',
            'year_level' => 'required|integer|min:1',
            'section_id' => 'required',
            'days_of_week' => 'required|array|min:1',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);

        // Check for subject uniqueness within the same department and section
        $existingSchedule = Schedule::where('subject_id', $this->subject_id)
            ->where('section_id', $this->section_id)
            ->where('department_id', $this->department_id)
            ->where('id', '!=', $this->schedule->id) // Ignore current schedule
            ->first();

        if ($existingSchedule) {
            notyf()
                ->dismissible(true)
                ->position('x', 'right')
                ->position('y', 'top')
                ->error('This subject already exists for the selected section and department.');
            return;
        }

        // Check for schedule conflicts
        $conflicts = $this->getConflicts(
            $this->instructor_id,
            $this->section_id,
            $this->days_of_week,
            $this->start_time,
            $this->end_time,
            $this->schedule->id
        );

        if ($conflicts->isNotEmpty()) {
            $this->conflicts = $conflicts;
            notyf()
                ->dismissible(true)
                ->position('x', 'right')
                ->position('y', 'top')
                ->error('Oops! There are conflicting schedules.');
            return;
        }

        // Update the schedule without modifying 'year_level'
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

        // Release lock if held by the current user
        if ($this->schedule->isLockedBy(Auth::id())) {
            $this->schedule->releaseLock();
            event(new \App\Events\ModelUnlocked(Schedule::class, $this->schedule->id));
        }

        notyf()
            ->dismissible(true)
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Schedule updated successfully');
        $this->dispatch('refresh-schedule-table');
        $this->resetForm();
    }

    /**
     * Generate a unique schedule code.
     *
     * @return string
     */
    protected function generateScheduleCode()
    {
        $lastSchedule = Schedule::orderBy('id', 'desc')->first();
        $newCodeNumber = $lastSchedule ? $lastSchedule->id + 1 + 100 : 100;
        return 'T' . str_pad($newCodeNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Check for schedule conflicts.
     *
     * @param int $instructor_id
     * @param int $section_id
     * @param array $days_of_week
     * @param string $start_time
     * @param string $end_time
     * @param int|null $ignoreScheduleId
     * @return \Illuminate\Database\Eloquent\Collection
     */
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
                $query->where('start_time', '<', $end_time)
                    ->where('end_time', '>', $start_time);
            });

        if ($ignoreScheduleId) {
            $query->where('id', '!=', $ignoreScheduleId);
        }

        return $query->get();
    }

    /**
     * Reset form fields and errors.
     *
     * @return void
     */
    public function resetForm()
    {
        $this->reset([
            'subject_id',
            'instructor_id',
            'laboratory_id',
            'college_id',
            'department_id',
            'year_level',
            'section_id',
            'days_of_week',
            'start_time',
            'end_time',
            'conflicts',
        ]);
        $this->resetErrorBag();
        $this->resetValidation();
        $this->editForm = false;
        $this->formTitle = 'Create Schedule';
    }

    /**
     * Close the modal and reset the form.
     *
     * @return void
     */
    #[On('reset-modal')]
    public function close()
    {
        // Release lock if held by the current user
        if ($this->editForm && $this->schedule && $this->schedule->isLockedBy(Auth::id())) {
            $this->schedule->releaseLock();
            event(new \App\Events\ModelUnlocked(Schedule::class, $this->schedule->id));
        }

        $this->resetForm();
    }

    /**
     * Enter edit mode with existing schedule data.
     *
     * @param int $id
     * @return void
     */
    #[On('edit-mode')]
    public function edit($id)
    {
        $this->formTitle = 'Edit Schedule';
        $this->editForm = true;
        $this->schedule = Schedule::findOrFail($id);

        // Check if the schedule is locked by another user
        if ($this->schedule->isLocked() && !$this->schedule->isLockedBy(Auth::id())) {
            $lockDetails = $this->schedule->lockDetails();
            $lockedByName = $lockDetails['user'] ? $lockDetails['user']->full_name : 'another user';
            $timeAgo = $lockDetails['timeAgo'];

            $this->lockError = "This schedule is currently being edited by {$lockedByName} ({$timeAgo}). Please try again later.";
            return;
        }

        // Lock the schedule for the current user
        $this->schedule->applyLock(Auth::id());
        $this->lockError = null;

        // Broadcast lock event
        event(new \App\Events\ModelLocked(Schedule::class, $this->schedule->id, Auth::id(), Auth::user()->full_name));

        // Subscribe to lock updates
        $this->dispatch('subscribe-to-lock-channel', [
            'modelClass' => base64_encode(Schedule::class),
            'modelId' => $this->schedule->id,
        ]);

        $this->subject_id = $this->schedule->subject_id;
        $this->instructor_id = $this->schedule->instructor_id;
        $this->laboratory_id = $this->schedule->laboratory_id;
        $this->college_id = $this->schedule->college_id;
        $this->department_id = $this->schedule->department_id;
        $this->year_level = $this->schedule->section->year_level; // Set year_level based on section
        $this->section_id = $this->schedule->section_id;
        $this->days_of_week = json_decode($this->schedule->days_of_week, true);
        $this->start_time = $this->schedule->start_time;
        $this->end_time = $this->schedule->end_time;
    }

    /**
     * Reset dependent properties when College changes.
     *
     * @return void
     */
    public function updatedCollegeId()
    {
        $this->department_id = null;
        $this->year_level = null;
        $this->section_id = null;
    }

    /**
     * Reset dependent properties when Department changes.
     *
     * @return void
     */
    public function updatedDepartmentId()
    {
        $this->year_level = null;
        $this->section_id = null;
    }

    /**
     * Reset dependent properties when Year Level changes.
     *
     * @return void
     */
    public function updatedYear_level()
    {
        $this->section_id = null;
    }
}
