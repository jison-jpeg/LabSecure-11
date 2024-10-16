<?php

namespace App\Livewire;

use App\Models\Subject;
use App\Models\College;
use App\Models\Department;
use Livewire\Component;
use Livewire\Attributes\On;
use Flasher\Notyf\Prime\NotyfInterface;

class CreateSubject extends Component
{
    public $formTitle = 'Create Subject';
    public $editForm = false;
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
        $this->resetErrorBag();
        $this->reset();
    }

    #[On('edit-mode')]
    public function edit($id)
    {
        $this->formTitle = 'Edit Subject';
        $this->editForm = true;
        $this->subject = Subject::findOrFail($id);
        $this->name = $this->subject->name;
        $this->code = $this->subject->code;
        $this->description = $this->subject->description;
        $this->college_id = $this->subject->college_id;
        $this->department_id = $this->subject->department_id;
    }

    public function update()
    {
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

        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Subject updated successfully');
        $this->dispatch('refresh-subject-table');
        $this->reset();
    }
}
