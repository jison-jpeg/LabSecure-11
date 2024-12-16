<?php

namespace App\Livewire;

use App\Models\Subject;
use App\Models\College;
use App\Models\Department;
use Livewire\Component;
use Livewire\Attributes\On;
use Flasher\Notyf\Prime\NotyfInterface;
use Illuminate\Support\Facades\Auth;

class CreateSubject extends Component
{
    public $formTitle = 'Create Subject';
    public $editForm = false;
    public $lockError = null;
    public $subject;
    public $name;
    public $code;
    public $description;
    public $college_id;
    public $department_id;

    public function render()
    {
        return view('livewire.create-subject', [
            'colleges' => College::all(),
            'departments' => Department::all(),
        ]);
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, [
            'name' => 'required',
            'code' => 'required|unique:subjects,code,' . ($this->subject->id ?? 'NULL'),
            'description' => 'nullable',
            'college_id' => 'required|exists:colleges,id',
            'department_id' => 'required|exists:departments,id',
        ]);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required',
            'code' => 'required|unique:subjects,code,' . ($this->subject->id ?? 'NULL'),
            'description' => 'nullable',
            'college_id' => 'required|exists:colleges,id',
            'department_id' => 'required|exists:departments,id',
        ]);

        Subject::create([
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'college_id' => $this->college_id,
            'department_id' => $this->department_id,
        ]);

        $this->dispatch('refresh-subject-table');
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Subject created successfully');
        $this->reset();
    }

    #[On('reset-modal')]
    public function close()
    {
        // Release the lock if held by the current user
        if ($this->editForm && $this->subject && $this->subject->isLockedBy(Auth::id())) {
            $this->subject->releaseLock();
            event(new \App\Events\ModelUnlocked(Subject::class, $this->subject->id));
        }
        
        $this->resetErrorBag();
        $this->reset();
    }

    #[On('edit-mode')]
    public function edit($id)
    {
        $this->formTitle = 'Edit Subject';
        $this->editForm = true;
        $this->subject = Subject::findOrFail($id);

        // Check if the record is locked by another user
        if ($this->subject->isLocked() && !$this->subject->isLockedBy(Auth::id())) {
            $lockDetails = $this->subject->lockDetails();
            $lockedByName = $lockDetails['user'] ? $lockDetails['user']->full_name : 'another user';
            $timeAgo = $lockDetails['timeAgo'];

            $this->lockError = "This subject record is currently being edited by {$lockedByName} ({$timeAgo}). Please try again later.";
            return;
        }

        // Lock the record for the current user
        $this->subject->applyLock(Auth::id());
        $this->lockError = null;

        // Broadcast lock event
        event(new \App\Events\ModelLocked(Subject::class, $this->subject->id, Auth::id(), Auth::user()->full_name));

        // Subscribe to lock updates
        $this->dispatch('subscribe-to-lock-channel', [
            'modelClass' => base64_encode(Subject::class),
            'modelId' => $this->subject->id,
        ]);
        
        $this->name = $this->subject->name;
        $this->code = $this->subject->code;
        $this->description = $this->subject->description;
        $this->college_id = $this->subject->college_id;
        $this->department_id = $this->subject->department_id;
    }

    public function update()
    {
        // Check if the record is locked by another user
        if ($this->subject->isLocked() && !$this->subject->isLockedBy(Auth::id())) {
            $lockDetails = $this->subject->lockDetails();
            $lockedByName = $lockDetails['user'] ? $lockDetails['user']->full_name : 'another user';
            $timeAgo = $lockDetails['timeAgo'];

            notyf()
                ->position('x', 'right')
                ->position('y', 'top')
                ->error("This subject record is currently being edited by {$lockedByName} ({$timeAgo}). Please try again later.");
            return;
        }

        $this->validate([
            'name' => 'required',
            'code' => 'required|unique:subjects,code,' . $this->subject->id,
            'description' => 'nullable',
            'college_id' => 'required|exists:colleges,id',
            'department_id' => 'required|exists:departments,id',
        ]);

        $this->subject->update([
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'college_id' => $this->college_id,
            'department_id' => $this->department_id,
        ]);

        // Release the lock if held by the current user
        if ($this->subject->isLockedBy(Auth::id())) {
            $this->subject->releaseLock();
            event(new \App\Events\ModelUnlocked(Subject::class, $this->subject->id));
        }

        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Subject updated successfully');
        $this->dispatch('refresh-subject-table');
        $this->reset();
    }
}
