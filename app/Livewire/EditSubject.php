<?php

namespace App\Livewire;

use App\Models\College;
use App\Models\Department;
use App\Models\Subject;
use Livewire\Component;
use Flasher\Notyf\Prime\NotyfInterface;

class EditSubject extends Component
{
    public $subjectId;
    public $code;
    public $name;
    public $description;
    public $collegeId;
    public $departmentId;
    public $departments = [];

    public function mount(Subject $subject)
    {
        $this->subjectId = $subject->id;
        $this->code = $subject->code;
        $this->name = $subject->name;
        $this->description = $subject->description;
        $this->collegeId = $subject->college_id;
        $this->departmentId = $subject->department_id;

        // Load the initial departments based on the selected college
        $this->departments = Department::where('college_id', $this->collegeId)->get();
    }

    public function updatedCollegeId($collegeId)
    {
        // Fetch departments related to the selected college
        $this->departments = Department::where('college_id', $collegeId)->get();
        $this->departmentId = null; // Reset department selection when college changes
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

        // Dispatch event to refresh the subject table
        $this->dispatch('refresh-subject-table');
        notyf()->position('x', 'right')->position('y', 'top')->success('Subject updated successfully');

        // Close the modal
        $this->dispatch('closeModal');
    }

    public function render()
    {
        return view('livewire.edit-subject', [
            'colleges' => College::all(),
            'departments' => $this->departments,
        ]);
    }
}
