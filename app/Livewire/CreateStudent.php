<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\College;
use App\Models\Department;
use App\Models\Section;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Hash;
use Flasher\Notyf\Prime\NotyfInterface;

class CreateStudent extends Component
{
    public $formTitle = 'Create Student';
    public $editForm = false;
    public $user;
    public $first_name;
    public $middle_name;
    public $last_name;
    public $username;
    public $email;
    public $password;
    public $college_id;
    public $department_id;
    public $section_id;

    public function render()
    {
        return view('livewire.create-student', [
            'colleges' => College::all(),
            'departments' => Department::all(),
            'sections' => Section::all(),
        ]);
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, [
            'first_name' => 'required',
            'last_name' => 'required',
            'username' => 'required|unique:users,username,' . ($this->user->id ?? 'NULL'),
            'email' => 'required|email|unique:users,email,' . ($this->user->id ?? 'NULL'),
            'password' => 'required|min:6',
            'college_id' => 'required|exists:colleges,id',
            'department_id' => 'required|exists:departments,id',
            'section_id' => 'required|exists:sections,id',
        ]);
    }

    public function save()
    {
        $this->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'username' => 'required|unique:users,username,' . ($this->user->id ?? 'NULL'),
            'email' => 'required|email|unique:users,email,' . ($this->user->id ?? 'NULL'),
            'password' => 'required|min:6',
            'college_id' => 'required|exists:colleges,id',
            'department_id' => 'required|exists:departments,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        User::create([
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role_id' => 3, // Assuming role_id 3 is for students
            'college_id' => $this->college_id,
            'department_id' => $this->department_id,
            'section_id' => $this->section_id,
        ]);

        $this->dispatch('refresh-student-table');
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Student created successfully');
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
        $this->formTitle = 'Edit Student';
        $this->editForm = true;
        $this->user = User::findOrFail($id);
        $this->first_name = $this->user->first_name;
        $this->middle_name = $this->user->middle_name;
        $this->last_name = $this->user->last_name;
        $this->username = $this->user->username;
        $this->email = $this->user->email;
        $this->college_id = $this->user->college_id;
        $this->department_id = $this->user->department_id;
        $this->section_id = $this->user->section_id;
    }

    public function update()
    {
        $this->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'username' => 'required|unique:users,username,' . $this->user->id,
            'email' => 'required|email|unique:users,email,' . $this->user->id,
            'college_id' => 'required|exists:colleges,id',
            'department_id' => 'required|exists:departments,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $this->user->update([
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'email' => $this->email,
            'college_id' => $this->college_id,
            'department_id' => $this->department_id,
            'section_id' => $this->section_id,
        ]);

        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Student updated successfully');
        $this->dispatch('refresh-student-table');
        $this->reset();
    }
}
