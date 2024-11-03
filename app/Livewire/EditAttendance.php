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

    public function save()
    {
        $this->validate([
            'status' => 'required|in:present,absent,late,excused,incomplete',
            'remarks' => 'nullable|string|max:255',
        ]);

        Attendance::create([
            'status' => $this->status,
            'remarks' => $this->remarks,
        ]);

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

    public function update()
    {
        $this->validate([
            'status' => 'required|in:present,absent,late,excused,incomplete',
            'remarks' => 'nullable|string|max:255',
        ]);

        $this->attendance->update([
            'status' => $this->status,
            'remarks' => $this->remarks,
        ]);

        notyf()->position('x', 'right')->position('y', 'top')->success('Attendance updated successfully');
        $this->dispatch('refresh-attendance-table');
        $this->reset();
    }
}
