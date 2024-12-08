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

        $this->attendance = Attendance::firstOrCreate(
            [
                'user_id' => $userId,
                'schedule_id' => $scheduleId,
                'date' => $date,
            ],
            [
                'status' => $this->status,
                'remarks' => 'No records',
                'percentage' => 0,
            ]
        );

        $this->formTitle = $this->attendance->wasRecentlyCreated ? 'Create Attendance' : 'Edit Attendance';
        $this->editForm = !$this->attendance->wasRecentlyCreated;

        $this->status = $this->attendance->status;
        $this->remarks = $this->attendance->remarks;
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
            $this->attendance->update([
                'status' => $this->status,
                'remarks' => $this->remarks,
                'percentage' => $this->status === 'present' ? 100 : ($this->status === 'absent' ? 0 : $this->attendance->percentage),
            ]);

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

        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Attendance updated successfully');
    }

    #[On('reset-modal')]
    public function close()
    {
        $this->resetErrorBag();
        $this->reset();
    }
}
