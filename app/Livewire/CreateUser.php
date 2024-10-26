<?php

namespace App\Livewire;

use App\Mail\UserCredentials;
use App\Models\TransactionLog;
use App\Models\User;
use App\Models\Role;
use App\Models\College;
use App\Models\Department;
use App\Models\Section;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CreateUser extends Component
{
    public $formTitle = 'Create User';
    public $editForm = false;
    public $user;
    public $first_name;
    public $middle_name;
    public $last_name;
    public $suffix;
    public $username;
    public $role_id;
    public $email;
    public $password;
    public $selectedCollege = null;
    public $selectedDepartment = null;
    public $selectedSection = null;
    public $colleges = [];
    public $departments = [];
    public $sections = [];
    public $roles = [];

    public function mount()
    {
        $this->roles = Role::all();
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

    public function updatedRoleId()
    {
        if ($this->isRoleAdmin()) {
            $this->reset(['selectedCollege', 'selectedDepartment', 'selectedSection']);
        } else {
            $this->loadInitialData();
        }
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, [
            'first_name' => 'required',
            'last_name' => 'required',
            'username' => 'required|unique:users,username,' . ($this->user->id ?? 'NULL'),
            'role_id' => 'required',
            'email' => 'required|email|unique:users,email,' . ($this->user->id ?? 'NULL'),
            'password' => 'nullable|min:6',
        ]);
    }

    public function save()
    {
        $this->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'username' => 'required|unique:users',
            'role_id' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'nullable|min:6',
        ]);

        if (empty($this->password)) {
            $this->password = Str::random(10);
        }

        $user = User::create([
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'suffix' => $this->suffix,
            'username' => $this->username,
            'role_id' => $this->role_id,
            'college_id' => $this->selectedCollege,
            'department_id' => $this->selectedDepartment,
            'section_id' => $this->selectedSection,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        TransactionLog::create([
            'user_id' => Auth::id(), 
            'action' => 'create',
            'model' => 'User',
            'model_id' => $user->id,
            'details' => json_encode([
                'user' => $user->full_name,
                'username' => $user->username,
            ]),
        ]);

        Mail::to($user->email)->queue(new UserCredentials($user, $this->password));
        
        $this->dispatch('refresh-user-table');
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('User created successfully');
        $this->resetFields();
    }

    public function resetFields()
    {
        $this->reset([
            'first_name', 'middle_name', 'last_name', 'suffix', 'username', 'role_id', 'email', 
            'password', 'selectedCollege', 'selectedDepartment', 'selectedSection'
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
        $this->formTitle = 'Edit User';
        $this->editForm = true;
        $this->user = User::findOrFail($id);
        $this->first_name = $this->user->first_name;
        $this->middle_name = $this->user->middle_name;
        $this->last_name = $this->user->last_name;
        $this->suffix = $this->user->suffix;
        $this->username = $this->user->username;
        $this->role_id = $this->user->role_id;
        $this->email = $this->user->email;
        $this->selectedCollege = $this->user->college_id;
        $this->selectedDepartment = $this->user->department_id;
        $this->selectedSection = $this->user->section_id;
        $this->updatedSelectedCollege($this->selectedCollege);
        $this->updatedSelectedDepartment($this->selectedDepartment);
    }

    public function update()
    {
        $this->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'username' => 'required|unique:users,username,' . $this->user->id,
            'role_id' => 'required',
            'email' => 'required|email|unique:users,email,' . $this->user->id,
            'password' => 'nullable|min:6',
        ]);

        $this->user->update([
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'suffix' => $this->suffix,
            'username' => $this->username,
            'role_id' => $this->role_id,
            'college_id' => $this->selectedCollege,
            'department_id' => $this->selectedDepartment,
            'section_id' => $this->selectedSection,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        TransactionLog::create([
            'user_id' => Auth::id(), 
            'action' => 'update',
            'model' => 'User',
            'model_id' => $this->user->id,
            'details' => json_encode([
                'user' => $this->user->full_name,
                'username' => $this->user->username,
            ]),
        ]);

        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('User updated successfully');
        $this->dispatch('refresh-user-table');
        $this->resetFields();
    }

    public function isRoleAdmin()
    {
        return $this->role_id && $this->role_id == Role::where('name', 'admin')->value('id');
    }

    public function isRoleStudent()
    {
        return $this->role_id && $this->role_id == Role::where('name', 'student')->value('id');
    }

    public function render()
    {
        return view('livewire.create-user', [
            'roles' => $this->roles,
            'colleges' => $this->colleges,
            'departments' => $this->departments,
            'sections' => $this->sections,
        ]);
    }
}
