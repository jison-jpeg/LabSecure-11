<?php

namespace App\Livewire;

use App\Models\College;
use App\Models\Department;
use Livewire\Component;
use App\Models\Subject;
use Flasher\Notyf\Prime\NotyfInterface;

class EditSubject extends Component
{
    public $subjectId;
    public $code;
    public $name;
    public $description;
    public $collegeId;
    public $departmentId;

    public function mount(Subject $subject)
    {
        $this->subjectId = $subject->id;
        $this->code = $subject->code;
        $this->name = $subject->name;
        $this->description = $subject->description;
        $this->collegeId = $subject->college_id;
        $this->departmentId = $subject->department_id;
    }

    public function update()
{
    $this->validate([
        'code' => 'required|string|max:10',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:500',
        'collegeId' => 'required|exists:colleges,id',
        'departmentId' => 'required|exists:departments,id',
    ]);

    $subject = Subject::findOrFail($this->subjectId);
    $subject->update([
        'code' => $this->code,
        'name' => $this->name,
        'description' => $this->description,
        'college_id' => $this->collegeId,
        'department_id' => $this->departmentId,
    ]);

    $this->dispatch('refresh-subject-table'); // Refresh the subject table
    notyf()->position('x', 'right')->position('y', 'top')->success('Subject updated successfully');

    $this->dispatch('closeModal'); // Close the modal after update
}
    public function render()
    {
        return view('livewire.edit-subject', [
            'colleges' => College::all(),
            'departments' => Department::all(),
        ]);
    }
}
