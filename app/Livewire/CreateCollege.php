<?php

namespace App\Livewire;

use App\Models\College;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Validator;
use Flasher\Notyf\Prime\NotyfInterface;
use App\Models\TransactionLog;
use Illuminate\Support\Facades\Auth;

class CreateCollege extends Component
{
    public $formTitle = 'Create College';
    public $editForm = false;
    public $lockError = null;
    public $college;
    public $name;
    public $description; // Added description field

    public function render()
    {
        return view('livewire.create-college');
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, [
            'name' => 'required|unique:colleges,name,' . ($this->college->id ?? 'NULL'),
            'description' => 'nullable|string|max:1000'
        ]);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|unique:colleges,name,' . ($this->college->id ?? 'NULL'),
            'description' => 'nullable|string|max:1000'
        ]);

        $college = College::create([
            'name' => $this->name,
            'description' => $this->description, // Save description
        ]);

        // Log the creation of the college
        TransactionLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'model' => 'College',
            'model_id' => $college->id,
            'details' => json_encode([
                'college_name' => $college->name,
                'description' => $college->description, // Include description in log
                'user' => Auth::user()->full_name,
                'username' => Auth::user()->username,
            ]),
        ]);

        $this->dispatch('refresh-college-table');
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('College created successfully');
        $this->reset(['name', 'description']); // Reset description
    }

    #[On('reset-modal')]
    public function close()
    {
        // Release the lock if currently locked by this user
        if ($this->editForm && $this->college && $this->college->isLockedBy(Auth::id())) {
            $this->college->releaseLock();
            // Broadcast that the college is unlocked
            event(new \App\Events\ModelUnlocked(College::class, $this->college->id));
        }

        $this->resetErrorBag();
        $this->reset();
        $this->editForm = false; // Reset edit mode to false
        $this->formTitle = 'Create College'; // Reset the title
    }

    #[On('edit-mode')]
    public function edit($id)
    {
        $this->formTitle = 'Edit College';
        $this->editForm = true;
        $this->college = College::findOrFail($id);

        // Attempt to lock the college
        if ($this->college->isLocked() && !$this->college->isLockedBy(Auth::id())) {
            // Retrieve lock details
            $lockDetails = $this->college->lockDetails();
            $lockedByName = $lockDetails['user'] ? $lockDetails['user']->full_name : 'another user';
            $timeAgo = $lockDetails['timeAgo'];

            // Set the lock error message
            $this->lockError = "This college is currently being edited by {$lockedByName} ({$timeAgo}). You cannot edit it now.";
            return; // Stop here to prevent loading form fields.
        } else {
            // Lock the record for the current user
            $this->college->applyLock(Auth::id());
            $this->lockError = null;

            // Broadcast that the college is locked
            event(new \App\Events\ModelLocked(College::class, $this->college->id, Auth::id(), Auth::user()->full_name));

            // Subscribe to lock updates for this college
            $this->dispatch('subscribe-to-lock-channel', [
                'modelClass' => base64_encode(College::class),
                'modelId' => $this->college->id,
            ]);
        }

        $this->name = $this->college->name;
        $this->description = $this->college->description; // Load description for editing
    }

    public function update()
    {
        // Check if the college is locked by another user
        if ($this->college->isLocked() && !$this->college->isLockedBy(Auth::id())) {
            // Retrieve lock details
            $lockDetails = $this->college->lockDetails();
            $lockedByName = $lockDetails['user'] ? $lockDetails['user']->full_name : 'another user';
            $timeAgo = $lockDetails['timeAgo'];

            // Notify the user that the record is locked
            notyf()
                ->position('x', 'right')
                ->position('y', 'top')
                ->error("This college is currently being edited by {$lockedByName} ({$timeAgo}). Please try again later.");
            return;
        }

        $this->validate([
            'name' => 'required|unique:colleges,name,' . $this->college->id,
            'description' => 'nullable|string|max:1000'
        ]);

        $this->college->update([
            'name' => $this->name,
            'description' => $this->description, // Update description
        ]);

        // Log the update of the college
        TransactionLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'model' => 'College',
            'model_id' => $this->college->id,
            'details' => json_encode([
                'college_name' => $this->college->name,
                'description' => $this->college->description, // Include description in log
                'user' => Auth::user()->full_name,
                'username' => Auth::user()->username,
            ]),
        ]);

        // Release the lock if currently locked by this user
        if ($this->college->isLockedBy(Auth::id())) {
            $this->college->releaseLock();
            // Broadcast that the college is unlocked
            event(new \App\Events\ModelUnlocked(College::class, $this->college->id));
        }

        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('College updated successfully');
        $this->dispatch('refresh-college-table');
        $this->reset(['name', 'description']); // Reset description
    }
}
