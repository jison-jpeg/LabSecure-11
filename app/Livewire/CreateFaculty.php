<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\College;
use App\Models\Role;
use App\Models\Department;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Flasher\Notyf\Prime\NotyfInterface;
use App\Models\TransactionLog;

class CreateFaculty extends Component
{
    public $formTitle = 'Create Faculty';
    public $editForm = false;
    public $lockError = null;
    public $user;
    public $first_name;
    public $middle_name;
    public $last_name;
    public $username;
    public $email;
    public $password;
    public $college_id;
    public $department_id;

    public $departments = []; // Dynamic Departments based on selected College

    public function render()
    {
        return view('livewire.create-faculty', [
            'colleges' => College::all(),
            'departments' => $this->departments,
        ]);
    }

    /**
     * Lifecycle hook that runs when any property is updated.
     * Used here for validation.
     */
    public function updated($propertyName)
    {
        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'username' => 'required|unique:users,username,' . ($this->user->id ?? 'NULL'),
            'email' => 'required|email|unique:users,email,' . ($this->user->id ?? 'NULL'),
            'password' => $this->editForm ? 'nullable|min:6' : 'required|min:6',
            'college_id' => 'required|exists:colleges,id',
            'department_id' => [
                'required',
                'exists:departments,id',
                function ($attribute, $value, $fail) {
                    if ($this->college_id && Department::where('id', $value)->where('college_id', $this->college_id)->doesntExist()) {
                        $fail('The selected department does not belong to the selected college.');
                    }
                },
            ],
        ];

        $this->validateOnly($propertyName, $rules);
    }

    /**
     * Save a new Faculty member.
     */
    public function save()
    {
        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'username' => 'required|unique:users,username,' . ($this->user->id ?? 'NULL'),
            'email' => 'required|email|unique:users,email,' . ($this->user->id ?? 'NULL'),
            'password' => 'required|min:6',
            'college_id' => 'required|exists:colleges,id',
            'department_id' => [
                'required',
                'exists:departments,id',
                function ($attribute, $value, $fail) {
                    if ($this->college_id && Department::where('id', $value)->where('college_id', $this->college_id)->doesntExist()) {
                        $fail('The selected department does not belong to the selected college.');
                    }
                },
            ],
        ];

        $this->validate($rules);

        // Fetch the role ID by role name
        $facultyRole = Role::where('name', 'instructor')->firstOrFail();

        $faculty = User::create([
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role_id' => $facultyRole->id, // Dynamically set role ID
            'college_id' => $this->college_id,
            'department_id' => $this->department_id,
        ]);

        // Log the creation of the faculty
        TransactionLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'model' => 'User',
            'model_id' => $faculty->id,
            'details' => json_encode([
                'user' => $faculty->full_name,
                'username' => $faculty->username,
                'college' => $faculty->college->name,
                'department' => $faculty->department->name,
                'created_by' => Auth::user()->full_name,
            ]),
        ]);

        $this->dispatch('refresh-faculty-table');
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Faculty created successfully');
        $this->resetForm();
    }

    /**
     * Reset the form fields.
     */
    private function resetForm()
    {
        $this->reset([
            'first_name',
            'middle_name',
            'last_name',
            'username',
            'email',
            'password',
            'college_id',
            'department_id',
            'departments',
        ]);
        $this->editForm = false;
        $this->user = null;
    }

    /**
     * Handle the 'reset-modal' event to reset the form.
     */
    #[On('reset-modal')]
    public function close()
    {
        // Release the lock if held by the current user
        if ($this->editForm && $this->user && $this->user->isLockedBy(Auth::id())) {
            $this->user->releaseLock();
            event(new \App\Events\ModelUnlocked(User::class, $this->user->id));
        }

        $this->resetErrorBag();
        $this->resetForm();
    }

    /**
     * Enter edit mode with the selected Faculty's data.
     */
    #[On('edit-mode')]
    public function edit($id)
    {
        $this->formTitle = 'Edit Faculty';
        $this->editForm = true;
        $this->user = User::findOrFail($id);

        // Check if the record is locked by another user
        if ($this->user->isLocked() && !$this->user->isLockedBy(Auth::id())) {
            $lockDetails = $this->user->lockDetails();
            $lockedByName = $lockDetails['user'] ? $lockDetails['user']->full_name : 'another user';
            $timeAgo = $lockDetails['timeAgo'];

            $this->lockError = "This faculty record is currently being edited by {$lockedByName} ({$timeAgo}). Please try again later.";
            return;
        }

        // Lock the record for the current user
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
        $this->username = $this->user->username;
        $this->email = $this->user->email;
        $this->college_id = $this->user->college_id;

        // Load Departments based on the Faculty's College
        $this->loadDepartments();

        $this->department_id = $this->user->department_id;
        $this->dispatch('subscribe-to-lock-channel', [
            'modelClass' => base64_encode(User::class),
            'modelId' => $this->user->id,
        ]);
    }

    /**
     * Update an existing Faculty member.
     */
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
                ->error("This faculty record is currently being edited by {$lockedByName} ({$timeAgo}). Please try again later.");
            return;
        }

        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'username' => 'required|unique:users,username,' . $this->user->id,
            'email' => 'required|email|unique:users,email,' . $this->user->id,
            'college_id' => 'required|exists:colleges,id',
            'department_id' => [
                'required',
                'exists:departments,id',
                function ($attribute, $value, $fail) {
                    if ($this->college_id && Department::where('id', $value)->where('college_id', $this->college_id)->doesntExist()) {
                        $fail('The selected department does not belong to the selected college.');
                    }
                },
            ],
        ];

        $this->validate($rules);

        $this->user->update([
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'email' => $this->email,
            'college_id' => $this->college_id,
            'department_id' => $this->department_id,
        ]);

        // Log the update of the faculty
        TransactionLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'model' => 'User',
            'model_id' => $this->user->id,
            'details' => json_encode([
                'user' => $this->user->full_name,
                'username' => $this->user->username,
                'college' => $this->user->college->name,
                'department' => $this->user->department->name,
                'updated_by' => Auth::user()->full_name,
            ]),
        ]);

        // Release the lock if held by the current user
        if ($this->user->isLockedBy(Auth::id())) {
            $this->user->releaseLock();
            event(new \App\Events\ModelUnlocked(User::class, $this->user->id));
        }

        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Faculty updated successfully');
        $this->dispatch('refresh-faculty-table');
        $this->resetForm();
    }

    /**
     * Fetch departments based on selected college.
     */
    private function loadDepartments()
    {
        if ($this->college_id) {
            $this->departments = Department::where('college_id', $this->college_id)->get();
        } else {
            $this->departments = collect();
        }
    }

    /**
     * Lifecycle hook to initialize component state.
     */
    public function mount()
    {
        $this->loadDepartments();
    }

    /**
     * Lifecycle hook that runs when the component is updating.
     * Specifically handles updates to the college_id.
     */
    public function updatedCollegeId()
    {
        $this->loadDepartments();
        $this->department_id = null; // Reset department selection
    }
}
