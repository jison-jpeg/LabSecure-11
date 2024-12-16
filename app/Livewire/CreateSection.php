<?php

namespace App\Livewire;

use App\Models\Section;
use App\Models\College;
use App\Models\Department;
use App\Models\TransactionLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Attributes\On;

class CreateSection extends Component
{
    public $formTitle = 'Create Section';
    public $editForm = false;
    public $lockError = null;
    public $section;
    public $name;
    public $college_id;
    public $department_id;
    public $year_level;
    public $semester;
    public $school_year;

    public function render()
    {
        return view('livewire.create-section', [
            'colleges' => College::all(),
            'departments' => Department::all(),
        ]);
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules());
    }

    public function rules()
    {
        return [
            'name' => [
                'required',
                Rule::unique('sections', 'name')
                    ->where(function ($query) {
                        return $query->where('year_level', $this->year_level)
                            ->where('department_id', $this->department_id);
                    })
                    ->ignore($this->section ? $this->section->id : null),
            ],
            'college_id' => 'required|exists:colleges,id',
            'department_id' => 'required|exists:departments,id',
            'year_level' => [
                'required',
                'string', // Now accepts any string including "1, 2, 3, 4, 5, 6, irregular"
                Rule::in(['1', '2', '3', '4', '5', '6', 'irregular']),
            ],
            'semester' => 'required',
            'school_year' => [
                'required',
                'regex:/^\d{4}-\d{4}$/',
                function ($attribute, $value, $fail) {
                    $years = explode('-', $value);
                    $currentYear = (int)date('Y');
                    $minYear = $currentYear - 3;
                    $maxYear = $currentYear + 3;

                    if (count($years) !== 2) {
                        $fail('The school year must be in the format YYYY-YYYY.');
                    } elseif ((int)$years[0] >= (int)$years[1]) {
                        $fail('The first year must be less than the second year.');
                    } elseif ((int)$years[0] < $minYear || (int)$years[1] > $maxYear) {
                        $fail("The school year must be between $minYear and $maxYear.");
                    }
                }
            ],
        ];
    }


    public function save()
    {
        $this->validate($this->rules());

        if ($this->editForm && $this->section) {
            // Update the existing section
            $this->section->update([
                'name' => $this->name,
                'college_id' => $this->college_id,
                'department_id' => $this->department_id,
                'year_level' => $this->year_level,
                'semester' => $this->semester,
                'school_year' => $this->school_year,
            ]);

            // Log the update action
            TransactionLog::create([
                'user_id' => Auth::id(),
                'action' => 'update',
                'model' => 'Section',
                'model_id' => $this->section->id,
                'details' => json_encode([
                    'section_name' => $this->name,
                    'college' => College::find($this->college_id)->name,
                    'department' => Department::find($this->department_id)->name,
                    'year_level' => $this->year_level,
                    'semester' => $this->semester,
                    'school_year' => $this->school_year,
                ]),
            ]);

            // Release lock if held by the current user
            if ($this->section->isLockedBy(Auth::id())) {
                $this->section->releaseLock();
                event(new \App\Events\ModelUnlocked(Section::class, $this->section->id));
            }

            notyf()
                ->position('x', 'right')
                ->position('y', 'top')
                ->success('Section updated successfully');
        } else {
            // Create a new section
            $section = Section::create([
                'name' => $this->name,
                'college_id' => $this->college_id,
                'department_id' => $this->department_id,
                'year_level' => $this->year_level,
                'semester' => $this->semester,
                'school_year' => $this->school_year,
            ]);

            // Log the creation action
            TransactionLog::create([
                'user_id' => Auth::id(),
                'action' => 'create',
                'model' => 'Section',
                'model_id' => $section->id,
                'details' => json_encode([
                    'section_name' => $this->name,
                    'college' => College::find($this->college_id)->name,
                    'department' => Department::find($this->department_id)->name,
                    'year_level' => $this->year_level,
                    'semester' => $this->semester,
                    'school_year' => $this->school_year,
                ]),
            ]);

            notyf()
                ->position('x', 'right')
                ->position('y', 'top')
                ->success('Section created successfully');
        }

        $this->dispatch('refresh-section-table');
        $this->reset();
        $this->editForm = false; // Exit edit mode
        $this->formTitle = 'Create Section'; // Reset the form title
    }

    public function update()
    {
        // Check if the section is locked by another user
        if ($this->section->isLocked() && !$this->section->isLockedBy(Auth::id())) {
            $lockDetails = $this->section->lockDetails();
            $lockedByName = $lockDetails['user'] ? $lockDetails['user']->full_name : 'another user';
            $timeAgo = $lockDetails['timeAgo'];

            notyf()
                ->position('x', 'right')
                ->position('y', 'top')
                ->error("This section is currently being edited by {$lockedByName} ({$timeAgo}). Please try again later.");
            return;
        }

        $this->validate($this->rules());

        // Update the section without combining year_level and name
        $this->section->update([
            'name' => $this->name,
            'college_id' => $this->college_id,
            'department_id' => $this->department_id,
            'year_level' => $this->year_level,
            'semester' => $this->semester,
            'school_year' => $this->school_year,
        ]);

        // Create transaction log for section update
        TransactionLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'model' => 'Section',
            'model_id' => $this->section->id,
            'details' => json_encode([
                'section_name' => $this->name, // Use name directly
                'college' => College::find($this->college_id)->name,
                'department' => Department::find($this->department_id)->name,
                'year_level' => $this->year_level,
                'semester' => $this->semester,
                'school_year' => $this->school_year,
            ]),
        ]);

        // Release lock if held by the current user
        if ($this->section->isLockedBy(Auth::id())) {
            $this->section->releaseLock();
            event(new \App\Events\ModelUnlocked(Section::class, $this->section->id));
        }

        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Section updated successfully');
        $this->dispatch('refresh-section-table');
        $this->reset();
    }

    #[On('reset-modal')]
    public function close()
    {

        // Release lock if held by the current user
        if ($this->editForm && $this->section && $this->section->isLockedBy(Auth::id())) {
            $this->section->releaseLock();
            event(new \App\Events\ModelUnlocked(Section::class, $this->section->id));
        }

        $this->resetErrorBag();
        $this->reset();
    }

    #[On('edit-mode')]
    public function edit($id)
    {
        $this->formTitle = 'Edit Section';
        $this->editForm = true;
        $this->section = Section::findOrFail($id);

        // Check if the section is locked by another user
        if ($this->section->isLocked() && !$this->section->isLockedBy(Auth::id())) {
            $lockDetails = $this->section->lockDetails();
            $lockedByName = $lockDetails['user'] ? $lockDetails['user']->full_name : 'another user';
            $timeAgo = $lockDetails['timeAgo'];

            $this->lockError = "This section is currently being edited by {$lockedByName} ({$timeAgo}). Please try again later.";
            return;
        }

        // Lock the section for the current user
        $this->section->applyLock(Auth::id());
        $this->lockError = null;

        // Broadcast lock event
        event(new \App\Events\ModelLocked(Section::class, $this->section->id, Auth::id(), Auth::user()->full_name));

        // Subscribe to lock updates
        $this->dispatch('subscribe-to-lock-channel', [
            'modelClass' => base64_encode(Section::class),
            'modelId' => $this->section->id,
        ]);

        $this->name = $this->section->name;
        $this->college_id = $this->section->college_id;
        $this->department_id = $this->section->department_id;
        $this->year_level = $this->section->year_level;
        $this->semester = $this->section->semester;
        $this->school_year = $this->section->school_year;
    }
}
