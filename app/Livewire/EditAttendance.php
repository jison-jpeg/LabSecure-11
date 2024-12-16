<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\TransactionLog;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class EditAttendance extends Component
{
    public $formTitle = 'Create Attendance';
    public $editForm = false;
    public $lockError = null;

    public $attendance;
    public $userId;
    public $scheduleId;
    public $status = 'absent'; // Default to 'absent'
    public $remarks;

    protected $listeners = ['edit-mode' => 'edit'];

    public function render()
    {
        return view('livewire.edit-attendance');
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, [
            'status' => 'required|in:present,absent,late,excused,incomplete',
            'remarks' => 'nullable|string|max:255',
        ]);
    }

    #[On('edit-mode')]
    public function edit($userId, $scheduleId, $date)
    {
        $this->userId = $userId;
        $this->scheduleId = $scheduleId;
    
        // Fetch existing attendance or initialize a new instance without saving
        $this->attendance = Attendance::where('user_id', $userId)
            ->where('schedule_id', $scheduleId)
            ->where('date', $date)
            ->first();
    
        if ($this->attendance) {
            // Attempt to lock the attendance
            if ($this->attendance->isLocked() && !$this->attendance->isLockedBy(Auth::id())) {
                // Retrieve lock details
                $lockDetails = $this->attendance->lockDetails();
                $lockedByName = $lockDetails['user'] ? $lockDetails['user']->full_name : 'another user';
                $timeAgo = $lockDetails['timeAgo'];
    
                // Set the lock error message
                $this->lockError = "This attendance record is currently being edited by {$lockedByName} ({$timeAgo}). You cannot edit it now.";
                return; // Stop here to prevent loading form fields.
            } else {
                // Lock the record for the current user
                $this->attendance->applyLock(Auth::id());
                $this->lockError = null;
    
                // Broadcast that the attendance is locked
                event(new \App\Events\ModelLocked(Attendance::class, $this->attendance->id, Auth::id(), Auth::user()->full_name));
    
                // Subscribe to lock updates for this attendance
                $this->dispatch('subscribe-to-lock-channel', [
                    'modelClass' => base64_encode(Attendance::class),
                    'modelId' => $this->attendance->id,
                ]);
            }
    
            // Existing record found
            $this->formTitle = 'Edit Attendance';
            $this->editForm = true;
            $this->status = $this->attendance->status;
            $this->remarks = $this->attendance->remarks;
        } else {
            // No existing record, prepare for creation
            $this->attendance = new Attendance([
                'user_id' => $userId,
                'schedule_id' => $scheduleId,
                'date' => $date,
                'status' => $this->status, // Default status
                'remarks' => 'No records', // Default remarks
                'percentage' => 0,         // Default percentage
            ]);
    
            $this->formTitle = 'Create Attendance';
            $this->editForm = false;
        }
    }
    


    public function save()
    {
        $this->validate([
            'status' => 'required|in:present,absent,late,excused,incomplete',
            'remarks' => 'nullable|string|max:255',
        ]);

        if ($this->editForm) {
            $this->update();
        } else {
            // Ensure all required fields are set before saving
            $this->attendance->fill([
                'user_id' => $this->userId, // Set the user_id
                'schedule_id' => $this->scheduleId, // Set the schedule_id
                'date' => $this->attendance->date ?? now()->toDateString(), // Set the date if not already set
                'status' => $this->status,
                'remarks' => $this->remarks,
                'percentage' => $this->status === 'present' ? 100 : ($this->status === 'absent' ? 0 : $this->attendance->percentage),
            ]);

            $this->attendance->save(); // Save the record with all required fields

            // Log the creation action
            TransactionLog::create([
                'user_id' => Auth::id(),
                'action' => 'create',
                'model' => 'Attendance',
                'model_id' => $this->attendance->id,
                'details' => json_encode([
                    'user_id' => $this->userId,
                    'schedule_id' => $this->scheduleId,
                    'status' => $this->status,
                    'remarks' => $this->remarks,
                ]),
            ]);

            notyf()
                ->position('x', 'right')
                ->position('y', 'top')
                ->success('Attendance created successfully');
        }

        $this->dispatch('refresh-attendance-table');
        $this->reset();
    }


    public function update()
    {
        // Check if the attendance exists and is locked by another user
        if ($this->attendance && $this->attendance->isLocked() && !$this->attendance->isLockedBy(Auth::id())) {
            // Retrieve lock details
            $lockDetails = $this->attendance->lockDetails();
            $lockedByName = $lockDetails['user'] ? $lockDetails['user']->full_name : 'another user';
            $timeAgo = $lockDetails['timeAgo'];
    
            // Notify the user that the record is locked
            notyf()
                ->position('x', 'right')
                ->position('y', 'top')
                ->error("This attendance record is currently being edited by {$lockedByName} ({$timeAgo}). Please try again later.");
            return;
        }
    
        $this->validate([
            'status' => 'required|in:present,absent,late,excused,incomplete',
            'remarks' => 'nullable|string|max:255',
        ]);
    
        $originalData = $this->attendance->only(['status', 'remarks', 'percentage']);
    
        $this->attendance->update([
            'status' => $this->status,
            'remarks' => $this->remarks,
            'percentage' => $this->status === 'present' ? 100 : ($this->status === 'absent' ? 0 : $this->attendance->percentage),
        ]);
    
        $changes = array_diff_assoc($this->attendance->only(['status', 'remarks', 'percentage']), $originalData);
    
        TransactionLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'model' => 'Attendance',
            'model_id' => $this->attendance->id,
            'details' => json_encode([
                'user_id' => $this->userId,
                'schedule_id' => $this->scheduleId,
                'changes' => $changes,
            ]),
        ]);
    
        // Release the lock if currently locked by this user
        if ($this->attendance && $this->attendance->isLockedBy(Auth::id())) {
            $this->attendance->releaseLock();
            // Broadcast that the attendance is unlocked
            event(new \App\Events\ModelUnlocked(Attendance::class, $this->attendance->id));
        }
    
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Attendance updated successfully');
    
        $this->dispatch('refresh-attendance-table');
        $this->reset();
    }
    

    #[On('reset-modal')]
    public function close()
    {
        // Release the lock if currently locked by this user
        if ($this->editForm && $this->attendance && $this->attendance->isLockedBy(Auth::id())) {
            $this->attendance->releaseLock();
            // Broadcast that the attendance is unlocked
            event(new \App\Events\ModelUnlocked(Attendance::class, $this->attendance->id));
        }
    
        $this->resetErrorBag();
        $this->reset();
    }
    
}
