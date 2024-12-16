<?php

namespace App\Livewire;

use App\Models\Department;
use App\Models\College;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Validator;
use Flasher\Notyf\Prime\NotyfInterface;
use Illuminate\Support\Facades\Auth;
use App\Models\TransactionLog;

class CreateDepartment extends Component
{
    public $formTitle = 'Create Department';
    public $editForm = false;
    public $lockError = null;
    public $department;
    public $description;
    public $name;
    public $college_id;

    public function render()
    {
        return view('livewire.create-department', [
            'colleges' => College::all(),
        ]);
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, [
            'name' => 'required|unique:departments,name,' . ($this->department->id ?? 'NULL'),
            'college_id' => 'required|exists:colleges,id',
            'description' => 'nullable|string|max:1000',
        ]);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|unique:departments,name,' . ($this->department->id ?? 'NULL'),
            'college_id' => 'required|exists:colleges,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $department = Department::create([
            'name' => $this->name,
            'college_id' => $this->college_id,
            'description' => $this->description,
        ]);

        // Log the creation of the department
        TransactionLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'model' => 'Department',
            'model_id' => $department->id,
            'details' => json_encode([
                'department_name' => $department->name,
                'college_name' => College::find($this->college_id)->name,
                'description' => $department->description,
                'user' => Auth::user()->full_name,
                'username' => Auth::user()->username,
            ]),
        ]);

        $this->dispatch('refresh-department-table');
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Department created successfully');
        $this->reset();
    }

    #[On('reset-modal')]
    public function close()
    {
        // Release the lock if currently locked by this user
        if ($this->editForm && $this->department && $this->department->isLockedBy(Auth::id())) {
            $this->department->releaseLock();
            // Broadcast that the department is unlocked
            event(new \App\Events\ModelUnlocked(Department::class, $this->department->id));
        }

        $this->resetErrorBag();
        $this->editForm = false;
        $this->formTitle = 'Create Department';
        $this->reset();
    }

    #[On('edit-department')]
    public function edit($id)
    {
        $this->formTitle = 'Edit Department';
        $this->editForm = true;
        $this->department = Department::findOrFail($id);

        // Attempt to lock the department
        if ($this->department->isLocked() && !$this->department->isLockedBy(Auth::id())) {
            // Retrieve lock details
            $lockDetails = $this->department->lockDetails();
            $lockedByName = $lockDetails['user'] ? $lockDetails['user']->full_name : 'another user';
            $timeAgo = $lockDetails['timeAgo'];

            // Set the lock error message
            $this->lockError = "This department is currently being edited by {$lockedByName} ({$timeAgo}). You cannot edit it now.";
            return; // Stop here to prevent loading form fields.
        } else {
            // Lock the record for the current user
            $this->department->applyLock(Auth::id());
            $this->lockError = null;

            // Broadcast that the department is locked
            event(new \App\Events\ModelLocked(Department::class, $this->department->id, Auth::id(), Auth::user()->full_name));

            // Subscribe to lock updates for this department
            $this->dispatch('subscribe-to-lock-channel', [
                'modelClass' => base64_encode(Department::class),
                'modelId' => $this->department->id,
            ]);
        }

        $this->name = $this->department->name;
        $this->college_id = $this->department->college_id;
        $this->description = $this->department->description;
    }

    public function update()
    {
        // Check if the department is locked by another user
        if ($this->department->isLocked() && !$this->department->isLockedBy(Auth::id())) {
            // Retrieve lock details
            $lockDetails = $this->department->lockDetails();
            $lockedByName = $lockDetails['user'] ? $lockDetails['user']->full_name : 'another user';
            $timeAgo = $lockDetails['timeAgo'];

            // Notify the user that the record is locked
            notyf()
                ->position('x', 'right')
                ->position('y', 'top')
                ->error("This department is currently being edited by {$lockedByName} ({$timeAgo}). Please try again later.");
            return;
        }
        $this->validate([
            'name' => 'required|unique:departments,name,' . $this->department->id,
            'college_id' => 'required|exists:colleges,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $this->department->update([
            'name' => $this->name,
            'college_id' => $this->college_id,
            'description' => $this->description,
        ]);

        // Log the update of the department
        TransactionLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'model' => 'Department',
            'model_id' => $this->department->id,
            'details' => json_encode([
                'department_name' => $this->department->name,
                'college_name' => College::find($this->college_id)->name,
                'description' => $this->department->description,
                'user' => Auth::user()->full_name,
                'username' => Auth::user()->username,
            ]),
        ]);

        // Release the lock if currently locked by this user
        if ($this->department->isLockedBy(Auth::id())) {
            $this->department->releaseLock();
            // Broadcast that the department is unlocked
            event(new \App\Events\ModelUnlocked(Department::class, $this->department->id));
        }

        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Department updated successfully');
        $this->dispatch('refresh-department-table');
        $this->reset();
    }
}
