<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Role;
use App\Models\College;
use App\Models\Department;
use App\Models\Section;
use App\Models\TransactionLog;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class CreateStudent extends Component
{
    public $formTitle = 'Create Student';
    public $editForm = false;
    public $lockError = null;
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
    public $departments = []; // Dynamic Departments based on selected College
    public $sections = []; // Dynamic Sections based on selected Department

    public function mount()
    {
        $this->loadInitialData();
    }

    public function loadInitialData()
    {
        $this->colleges = College::all();
        $this->departments = collect();
        $this->sections = collect();
    }

    public function updatedSelectedCollege($collegeId)
    {
        $this->departments = Department::where('college_id', $collegeId)->get();
        $this->selectedDepartment = null;
        $this->sections = collect();
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
            'selectedDepartment' => [
                'required',
                'exists:departments,id',
                function ($attribute, $value, $fail) {
                    if ($this->selectedCollege && Department::where('id', $value)->where('college_id', $this->selectedCollege)->doesntExist()) {
                        $fail('The selected department does not belong to the selected college.');
                    }
                },
            ],
            'selectedSection' => 'required|exists:sections,id',
            'status' => 'required|in:active,inactive',
        ]);
    }

    public function save()
    {
        $this->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'username' => 'required|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'selectedCollege' => 'required|exists:colleges,id',
            'selectedDepartment' => [
                'required',
                'exists:departments,id',
                function ($attribute, $value, $fail) {
                    if ($this->selectedCollege && Department::where('id', $value)->where('college_id', $this->selectedCollege)->doesntExist()) {
                        $fail('The selected department does not belong to the selected college.');
                    }
                },
            ],
            'selectedSection' => 'required|exists:sections,id',
            'status' => 'required|in:active,inactive',
        ]);

        // Fetch the 'student' role dynamically
        $studentRole = Role::where('name', 'student')->first();

        if (!$studentRole) {
            throw new \Exception('Student role not found.');
        }

        // Create the student
        $student = User::create([
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'suffix' => $this->suffix,
            'username' => $this->username,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role_id' => $studentRole->id,
            'college_id' => $this->selectedCollege,
            'department_id' => $this->selectedDepartment,
            'section_id' => $this->selectedSection,
            'status' => $this->status,
        ]);

        // Log the creation of the student
        TransactionLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'model' => 'User',
            'model_id' => $student->id,
            'details' => json_encode([
                'user' => $student->full_name,
                'username' => $student->username,
                'email' => $student->email,
                'college_name' => $student->college->name ?? 'N/A',
                'department_name' => $student->department->name ?? 'N/A',
                'section_name' => $student->section->name ?? 'N/A',
                'status' => $student->status,
            ]),
        ]);

        // Notify and reset
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
            'first_name',
            'middle_name',
            'last_name',
            'suffix',
            'username',
            'email',
            'password',
            'selectedCollege',
            'selectedDepartment',
            'selectedSection',
            'status'
        ]);
        $this->resetErrorBag();
        $this->departments = collect();
        $this->sections = collect();
    }

    #[On('reset-modal')]
    public function close()
    {
        // Release the lock if held by the current user
        if ($this->editForm && $this->user && $this->user->isLockedBy(Auth::id())) {
            $this->user->releaseLock();
            event(new \App\Events\ModelUnlocked(User::class, $this->user->id));
        }

        $this->resetFields();
        $this->loadInitialData();
    }

    #[On('edit-mode')]
    public function edit($id)
    {
        $this->formTitle = 'Edit Student';
        $this->editForm = true;
        $this->user = User::findOrFail($id);

        // Check if the record is locked by another user
        if ($this->user->isLocked() && !$this->user->isLockedBy(Auth::id())) {
            $lockDetails = $this->user->lockDetails();
            $lockedByName = $lockDetails['user'] ? $lockDetails['user']->full_name : 'another user';
            $timeAgo = $lockDetails['timeAgo'];

            $this->lockError = "This student record is currently being edited by {$lockedByName} ({$timeAgo}). Please try again later.";
            return;
        }

        // Apply lock for the current user
        $this->user->applyLock(Auth::id());
        $this->lockError = null;

        // Broadcast lock event
        event(new \App\Events\ModelLocked(User::class, $this->user->id, Auth::id(), Auth::user()->full_name));

        // Subscribe to lock updates
        $this->dispatch('subscribe-to-lock-channel', [
            'modelClass' => base64_encode(User::class),
            'modelId' => $this->user->id,
        ]);

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
        // Check if the record is locked by another user
        if ($this->user->isLocked() && !$this->user->isLockedBy(Auth::id())) {
            $lockDetails = $this->user->lockDetails();
            $lockedByName = $lockDetails['user'] ? $lockDetails['user']->full_name : 'another user';
            $timeAgo = $lockDetails['timeAgo'];

            notyf()
                ->position('x', 'right')
                ->position('y', 'top')
                ->error("This student record is currently being edited by {$lockedByName} ({$timeAgo}). Please try again later.");
            return;
        }

        $this->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'username' => 'required|unique:users,username,' . $this->user->id,
            'email' => 'required|email|unique:users,email,' . $this->user->id,
            'password' => 'nullable|min:6',
            'selectedCollege' => 'required|exists:colleges,id',
            'selectedDepartment' => [
                'required',
                'exists:departments,id',
                function ($attribute, $value, $fail) {
                    if ($this->selectedCollege && Department::where('id', $value)->where('college_id', $this->selectedCollege)->doesntExist()) {
                        $fail('The selected department does not belong to the selected college.');
                    }
                },
            ],
            'selectedSection' => 'required|exists:sections,id',
            'status' => 'required|in:active,inactive',
        ]);

        // Fetch the 'student' role dynamically
        $studentRole = Role::where('name', 'student')->first();

        if (!$studentRole) {
            throw new \Exception('Student role not found.');
        }

        // Capture original data before update
        $originalData = $this->user->getOriginal();

        // Capture original related model names
        $originalCollegeName = $this->user->college->name ?? 'N/A';
        $originalDepartmentName = $this->user->department->name ?? 'N/A';
        $originalSectionName = $this->user->section->name ?? 'N/A';
        $originalRoleName = $this->user->role->name ?? 'N/A';

        // Prepare update data
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
            'role_id' => $studentRole->id,
        ];

        if ($this->password) {
            $updateData['password'] = Hash::make($this->password);
        }

        // Update the student
        $this->user->update($updateData);

        // Capture new related model names after update
        $newCollegeName = $this->user->college->name ?? 'N/A';
        $newDepartmentName = $this->user->department->name ?? 'N/A';
        $newSectionName = $this->user->section->name ?? 'N/A';
        $newRoleName = $this->user->role->name ?? 'N/A';

        // Determine the changes made
        $changesArray = [];

        // Compare related models
        if ($originalCollegeName !== $newCollegeName) {
            $changesArray['college_name'] = [
                'old' => $originalCollegeName,
                'new' => $newCollegeName,
            ];
        }

        if ($originalDepartmentName !== $newDepartmentName) {
            $changesArray['department_name'] = [
                'old' => $originalDepartmentName,
                'new' => $newDepartmentName,
            ];
        }

        if ($originalSectionName !== $newSectionName) {
            $changesArray['section_name'] = [
                'old' => $originalSectionName,
                'new' => $newSectionName,
            ];
        }

        if ($originalRoleName !== $newRoleName) {
            $changesArray['role_name'] = [
                'old' => $originalRoleName,
                'new' => $newRoleName,
            ];
        }

        // Check other fields for changes
        $fieldsToCheck = ['first_name', 'middle_name', 'last_name', 'suffix', 'username', 'email', 'status'];
        foreach ($fieldsToCheck as $field) {
            $oldValue = $originalData[$field] ?? null;
            $newValue = $this->user->{$field};
            if ($oldValue !== $newValue) {
                $changesArray[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        // Log the update of the student
        TransactionLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'model' => 'User',
            'model_id' => $this->user->id,
            'details' => json_encode([
                'user' => $this->user->full_name,
                'username' => $this->user->username,
                'email' => $this->user->email,
                'college_name' => $newCollegeName,
                'department_name' => $newDepartmentName,
                'section_name' => $newSectionName,
                'status' => $this->user->status,
                'role_name' => $newRoleName,
                'updated_by' => Auth::user()->full_name,
                'changes' => $changesArray, // Include the changes array
            ]),
        ]);

        // Release the lock if held by the current user
        if ($this->user->isLockedBy(Auth::id())) {
            $this->user->releaseLock();
            event(new \App\Events\ModelUnlocked(User::class, $this->user->id));
        }

        // Notify and reset
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
