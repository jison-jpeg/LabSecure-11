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

    /**
     * Real-time validation as properties are updated.
     *
     * @param string $propertyName
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, [
            'status' => 'required|in:present,absent,late,excused,incomplete',
            'remarks' => 'nullable|string|max:255',
        ]);
    }

    /**
     * Save a new attendance record.
     *
     * @return void
     */
    public function save()
    {
        $this->validate([
            'status' => 'required|in:present,absent,late,excused,incomplete',
            'remarks' => 'nullable|string|max:255',
        ]);

        Attendance::create([
            'status' => $this->status,
            'remarks' => $this->remarks,
            // Add other necessary fields here, e.g., user_id, date, etc.
        ]);

        $this->dispatch('refresh-attendance-table');
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Attendance created successfully');
        $this->reset();
    }

    /**
     * Handle the 'reset-modal' event to reset the form.
     *
     * @return void
     */
    #[On('reset-modal')]
    public function close()
    {
        $this->resetErrorBag();
        $this->reset(['formTitle', 'editForm', 'attendance', 'status', 'remarks']);
    }

    /**
     * Handle the 'edit-mode' event to load a specific attendance record.
     *
     * @param int $id
     * @return void
     */
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
     * Update an existing attendance record.
     *
     * @return void
     */
    public function update()
    {
        $this->validate([
            'status' => 'required|in:present,absent,late,excused,incomplete',
            'remarks' => 'nullable|string|max:255',
        ]);

        $this->attendance->update([
            'status' => $this->status,
            'remarks' => $this->remarks,
            // Update other necessary fields here if needed
        ]);

        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Attendance updated successfully');
        $this->dispatch('refresh-attendance-table');
        $this->reset();
    }
}
