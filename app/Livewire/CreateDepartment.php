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
    public $department;
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
        ]);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|unique:departments,name,' . ($this->department->id ?? 'NULL'),
            'college_id' => 'required|exists:colleges,id',
        ]);

        $department = Department::create([
            'name' => $this->name,
            'college_id' => $this->college_id,
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
        $this->resetErrorBag();
        $this->reset();
    }

    #[On('edit-department')]
    public function edit($id)
    {
        $this->formTitle = 'Edit Department';
        $this->editForm = true;
        $this->department = Department::findOrFail($id);
        $this->name = $this->department->name;
        $this->college_id = $this->department->college_id;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|unique:departments,name,' . $this->department->id,
            'college_id' => 'required|exists:colleges,id',
        ]);

        $this->department->update([
            'name' => $this->name,
            'college_id' => $this->college_id,
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
                'user' => Auth::user()->full_name,
                'username' => Auth::user()->username,
            ]),
        ]);

        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Department updated successfully');
        $this->dispatch('refresh-department-table');
        $this->reset();
    }
}
