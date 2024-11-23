<?php

namespace App\Livewire;

use App\Models\Attendance;
use Livewire\Component;
use Livewire\Attributes\On;
use Flasher\Notyf\Prime\NotyfInterface;

class EditAttendance extends Component
{
    public $formTitle = 'Create Attendance';
    public $editForm = false;
    public $attendance;
    public $status;
    public $remarks;

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

    /**
     * Save a new Attendance record.
     *
     * @return void
     */
    public function save()
    {
        $this->validate([
            'status' => 'required|in:present,absent,late,excused,incomplete',
            'remarks' => 'nullable|string|max:255',
        ]);

        // Prepare data for creation
        $data = [
            'status' => $this->status,
            'remarks' => $this->remarks,
        ];

        // If status is 'present', set percentage to 100
        if ($this->status === 'present') {
            $data['percentage'] = 100;
        }

        Attendance::create($data);

        $this->dispatch('refresh-attendance-table');
        notyf()->position('x', 'right')->position('y', 'top')->success('Attendance created successfully');
        $this->reset();
    }

    #[On('reset-modal')]
    public function close()
    {
        $this->resetErrorBag();
        $this->reset(['formTitle', 'editForm', 'attendance', 'status', 'remarks']);
    }

    #[On('edit-mode')]
    public function edit($id)
    {
        $this->formTitle = 'Edit Attendance';
        $this->editForm = true;
        $this->attendance = Attendance::findOrFail($id);
        $this->status = $this->attendance->status;
        $this->remarks = $this->attendance->remarks;
    }

    /**
     * Update an existing Attendance record.
     *
     * @return void
     */
    public function update()
    {
        $this->validate([
            'status' => 'required|in:present,absent,late,excused,incomplete',
            'remarks' => 'nullable|string|max:255',
        ]);

        // Determine the updated percentage based on the status logic
        $currentStatus = $this->attendance->status;

        $data = [
            'status' => $this->status,
            'remarks' => $this->remarks,
            'percentage' => $this->attendance->percentage, // Default to current percentage
        ];

        if ($this->status === 'present' && $currentStatus !== 'present') {
            // Only set to 100 if not already present
            $data['percentage'] = 100;
        } elseif ($this->status === 'absent' && $currentStatus !== 'absent') {
            // Set to 0% if changing to absent and not already absent
            $data['percentage'] = 0;
        }

        // Update the attendance record
        $this->attendance->update($data);

        notyf()->position('x', 'right')->position('y', 'top')->success('Attendance updated successfully');
        $this->dispatch('refresh-attendance-table');
        $this->reset();
    }
}
