<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\College;
use App\Models\Department;
use App\Models\Section;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Hash;

class CreateStudent extends Component
{
    public $formTitle = 'Create Student';
    public $editForm = false;
    public $user;
    public $first_name;
    public $middle_name;
    public $last_name;
    public $suffix;
    public $username;
    public $email;
    public $password;
    public $status = 'active';
    public $selectedCollege = null;
    public $selectedDepartment = null;
    public $selectedSection = null;
    public $colleges = [];
    public $departments = [];
    public $sections = [];

    public function mount()
    {
        $this->loadInitialData();
    }

    public function loadInitialData()
    {
        $this->colleges = College::all();
        $this->departments = [];
        $this->sections = [];
    }

    public function updatedSelectedCollege($collegeId)
    {
        $this->departments = Department::where('college_id', $collegeId)->get();
        $this->selectedDepartment = null;
        $this->sections = [];
    }

    public function updatedSelectedDepartment($departmentId)
    {
        $this->sections = Section::where('department_id', $departmentId)->get();
        $this->selectedSection = null;
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, [
            'first_name' => 'required',
            'last_name' => 'required',
            'username' => 'required|unique:users,username,' . ($this->user->id ?? 'NULL'),
            'email' => 'required|email|unique:users,email,' . ($this->user->id ?? 'NULL'),
            'password' => $this->editForm ? 'nullable|min:6' : 'required|min:6',
            'selectedCollege' => 'required|exists:colleges,id',
            'selectedDepartment' => 'required|exists:departments,id',
            'selectedSection' => 'required|exists:sections,id',
            'status' => 'required|in:active,inactive',
        ]);
    }

    public function save()
    {
        $this->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'selectedCollege' => 'required|exists:colleges,id',
            'selectedDepartment' => 'required|exists:departments,id',
            'selectedSection' => 'required|exists:sections,id',
            'status' => 'required|in:active,inactive',
        ]);

        User::create([
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'suffix' => $this->suffix,
            'username' => $this->username,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role_id' => 3, // Assuming role_id 3 is for students
            'college_id' => $this->selectedCollege,
            'department_id' => $this->selectedDepartment,
            'section_id' => $this->selectedSection,
            'status' => $this->status,
        ]);

        $this->dispatch('refresh-student-table');
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Student created successfully');
        $this->resetFields();
    }

    public function resetFields()
    {
        $this->reset([
            'first_name', 'middle_name', 'last_name', 'suffix', 'username', 'email', 
            'password', 'selectedCollege', 'selectedDepartment', 'selectedSection', 'status'
        ]);
        $this->resetErrorBag();
    }

    #[On('reset-modal')]
    public function close()
    {
        $this->resetFields();
        $this->loadInitialData();
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
        $this->suffix = $this->user->suffix;
        $this->username = $this->user->username;
        $this->email = $this->user->email;
        $this->status = $this->user->status;

        // Set and load the college, department, and section
        $this->selectedCollege = $this->user->college_id;
        $this->departments = Department::where('college_id', $this->selectedCollege)->get();

        $this->selectedDepartment = $this->user->department_id;
        $this->sections = Section::where('department_id', $this->selectedDepartment)->get();

        $this->selectedSection = $this->user->section_id;
    }

    public function update()
    {
        $this->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'username' => 'required|unique:users,username,' . $this->user->id,
            'email' => 'required|email|unique:users,email,' . $this->user->id,
            'password' => 'nullable|min:6',
            'selectedCollege' => 'required|exists:colleges,id',
            'selectedDepartment' => 'required|exists:departments,id',
            'selectedSection' => 'required|exists:sections,id',
            'status' => 'required|in:active,inactive',
        ]);

        $updateData = [
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'suffix' => $this->suffix,
            'username' => $this->username,
            'email' => $this->email,
            'college_id' => $this->selectedCollege,
            'department_id' => $this->selectedDepartment,
            'section_id' => $this->selectedSection,
            'status' => $this->status,
        ];

        if ($this->password) {
            $updateData['password'] = Hash::make($this->password);
        }

        $this->user->update($updateData);

        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Student updated successfully');
        $this->dispatch('refresh-student-table');
        $this->resetFields();
    }

    public function render()
    {
        return view('livewire.create-student', [
            'colleges' => $this->colleges,
            'departments' => $this->departments,
            'sections' => $this->sections,
        ]);
    }
}
