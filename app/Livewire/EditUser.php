<?php

namespace App\Livewire;

use App\Models\TransactionLog;
use App\Models\User;
use App\Models\Role;
use App\Models\College;
use App\Models\Department;
use App\Models\Section;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EditUser extends Component
{
    public $formTitle = 'Edit User';
    public $lockError = null;
    public $user;
    public $first_name;
    public $middle_name;
    public $last_name;
    public $suffix;
    public $username;
    public $role_id;
    public $email;
    public $password;
    public $status = 'active';

    public $selectedCollege = null;
    public $selectedDepartment = null;
    public $selectedYearLevel = null;
    public $selectedSection = null;
    public $colleges = [];
    public $departments = [];
    public $yearLevels = [];
    public $sections = [];
    public $roles = [];

    protected $listeners = [
        'show-edit-user-modal' => 'openEditModal',
        'externalModelLocked' => 'handleExternalLock',
        'externalModelUnlocked' => 'handleExternalUnlock',
    ];

    public function mount(User $user)
    {
        $this->user = $user;
        $this->roles = Role::all();
        $this->loadInitialData();
        $this->loadUserData();
    }

    public function loadInitialData()
    {
        $this->colleges = College::all();
        $this->departments = $this->user->college_id 
            ? Department::where('college_id', $this->user->college_id)->get() 
            : [];
        $this->sections = $this->user->department_id 
            ? Section::where('department_id', $this->user->department_id)->get() 
            : [];
    }

    public function loadUserData()
    {
        $this->first_name = $this->user->first_name;
        $this->middle_name = $this->user->middle_name;
        $this->last_name = $this->user->last_name;
        $this->suffix = $this->user->suffix;
        $this->username = $this->user->username;
        $this->role_id = $this->user->role_id;
        $this->email = $this->user->email;
        $this->status = $this->user->status;
        $this->selectedCollege = $this->user->college_id;
        $this->selectedDepartment = $this->user->department_id;
        $this->selectedSection = $this->user->section_id;
        
        if ($this->selectedDepartment) {
            $this->yearLevels = Section::where('department_id', $this->selectedDepartment)
                                       ->pluck('year_level')
                                       ->unique()
                                       ->sort()
                                       ->values()
                                       ->toArray();
        }

        $this->selectedYearLevel = $this->user->section ? $this->user->section->year_level : null;

        if ($this->selectedYearLevel) {
            $this->sections = Section::where('department_id', $this->selectedDepartment)
                                     ->where('year_level', $this->selectedYearLevel)
                                     ->get();
        }
    }

    public function openEditModal()
    {
        // Attempt to lock the user record only when the modal is about to be shown.
        if ($this->user->isLocked() && !$this->user->isLockedBy(Auth::id())) {
            // The record is locked by another user
            $lockDetails = $this->user->lockDetails();
            $lockedByName = $lockDetails['user'] ? $lockDetails['user']->full_name : 'another user';
            $timeAgo = $lockDetails['timeAgo'];

            $this->lockError = "This user is currently being edited by {$lockedByName} ({$timeAgo}). You cannot edit it now.";
            return;
        }

        // Lock the record for the current user
        $this->user->applyLock(Auth::id());
        $this->lockError = null;

        // Broadcast that the user is locked
        event(new \App\Events\ModelLocked(User::class, $this->user->id, Auth::id(), Auth::user()->full_name));

        // Subscribe to lock channel for real-time updates
        $this->dispatch('subscribe-to-lock-channel', [
            'modelClass' => base64_encode(User::class),
            'modelId' => $this->user->id,
        ]);

        // Now that we have locked the record successfully, show the modal on the frontend
        $this->dispatch('show-edit-user-modal-trigger');
    }

    public function handleExternalLock($modelClass, $modelId, $lockedBy, $lockedByName)
    {
        // If we are editing the same user and another user locks it externally, show warning
        if ($this->user && $this->user->id == $modelId && $lockedBy != Auth::id()) {
            $this->lockError = "User {$lockedByName} is currently editing this record.";
        }
    }

    public function handleExternalUnlock($modelClass, $modelId)
    {
        // If the record was unlocked externally, clear the warning if we're editing it
        if ($this->user && $this->user->id == $modelId) {
            $this->lockError = null;
        }
    }

    public function updatedSelectedCollege($collegeId)
    {
        $this->departments = Department::where('college_id', $collegeId)->get();
        $this->reset(['selectedDepartment', 'selectedYearLevel', 'selectedSection']);
        $this->yearLevels = [];
        $this->sections = [];
    }

    public function updatedSelectedDepartment($departmentId)
    {
        $this->yearLevels = Section::where('department_id', $departmentId)
                                   ->pluck('year_level')
                                   ->unique()
                                   ->sort()
                                   ->values()
                                   ->toArray();
        $this->reset(['selectedYearLevel', 'selectedSection']);
        $this->sections = [];
    }

    public function updatedSelectedYearLevel($yearLevel)
    {
        $this->sections = Section::where('department_id', $this->selectedDepartment)
                                 ->where('year_level', $yearLevel)
                                 ->get();
        $this->selectedSection = null;
    }

    private function getValidationRules()
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'username'   => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($this->user->id),
            ],
            'role_id'    => 'required|exists:roles,id',
            'email'      => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user->id),
            ],
            'password'   => 'nullable|string|min:6',
            'status'     => 'required|in:active,inactive',
        ];

        if (!$this->isRoleAdmin()) {
            $rules['selectedCollege'] = ['required', 'exists:colleges,id'];
            
            if ($this->isRoleDean()) {
                $rules['selectedCollege'][] = function ($attribute, $value, $fail) {
                    $deanRoleId = Role::where('name', 'dean')->value('id');
                    $query = User::where('role_id', $deanRoleId)->where('college_id', $value);

                    if ($this->user) {
                        $query->where('id', '!=', $this->user->id);
                    }

                    if ($query->exists()) {
                        $fail('A dean has already been assigned to this college.');
                    }
                };
            }

            if ($this->isRoleChairperson() || $this->isRoleInstructor() || $this->isRoleStudent()) {
                $rules['selectedDepartment'] = ['required', 'exists:departments,id'];

                if ($this->isRoleChairperson()) {
                    $rules['selectedDepartment'][] = function ($attribute, $value, $fail) {
                        $chairRoleId = Role::where('name', 'chairperson')->value('id');
                        $query = User::where('role_id', $chairRoleId)->where('department_id', $value);

                        if ($this->user) {
                            $query->where('id', '!=', $this->user->id);
                        }

                        if ($query->exists()) {
                            $fail('A chairperson has already been assigned to this department.');
                        }
                    };
                }
            }

            if ($this->isRoleStudent()) {
                $rules['selectedYearLevel'] = ['required', 'in:' . implode(',', $this->yearLevels)];
                $rules['selectedSection'] = 'required|exists:sections,id';
            }
        }

        return $rules;
    }

    public function update()
    {
        // Check if locked by another user
        if ($this->user && $this->user->isLocked() && !$this->user->isLockedBy(Auth::id())) {
            $lockDetails = $this->user->lockDetails();
            $lockedByName = $lockDetails['user'] ? $lockDetails['user']->full_name : 'another user';
            $timeAgo = $lockDetails['timeAgo'];

            notyf()
                ->position('x', 'right')
                ->position('y', 'top')
                ->error("This record is currently being edited by {$lockedByName} ({$timeAgo}). Please try again later.");
            return;
        }

        $this->validate($this->getValidationRules());

        $data = [
            'first_name'    => $this->first_name,
            'middle_name'   => $this->middle_name,
            'last_name'     => $this->last_name,
            'suffix'        => $this->suffix,
            'username'      => $this->username,
            'role_id'       => $this->role_id,
            'email'         => $this->email,
            'status'        => $this->status,
            'college_id'    => $this->isRoleAdmin() ? null : $this->selectedCollege,
            'department_id' => ($this->isRoleChairperson() || $this->isRoleInstructor() || $this->isRoleStudent()) ? $this->selectedDepartment : null,
            'section_id'    => $this->isRoleStudent() ? $this->selectedSection : null,
        ];

        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        $this->user->update($data);

        TransactionLog::create([
            'user_id' => Auth::id(),
            'action'  => 'update',
            'model'   => 'User',
            'model_id'=> $this->user->id,
            'details' => json_encode([
                'user'     => $this->user->full_name,
                'username' => $this->user->username,
            ]),
        ]);

        // Release the lock if currently held
        if ($this->user->isLockedBy(Auth::id())) {
            $this->user->releaseLock();
            event(new \App\Events\ModelUnlocked(User::class, $this->user->id));
        }

        notyf()->position('x', 'right')->position('y', 'top')->success('User updated successfully');
        $this->dispatch('close-modal');
    }

    public function close()
    {
        // If currently holding lock, release it
        if ($this->user && $this->user->isLockedBy(Auth::id())) {
            $this->user->releaseLock();
            event(new \App\Events\ModelUnlocked(User::class, $this->user->id));
        }

        // Dispatch close event if needed
        $this->dispatch('close-modal');
    }

    public function isRoleAdmin() { return $this->role_id == Role::where('name', 'admin')->value('id'); }
    public function isRoleStudent() { return $this->role_id == Role::where('name', 'student')->value('id'); }
    public function isRoleChairperson() { return $this->role_id == Role::where('name', 'chairperson')->value('id'); }
    public function isRoleDean() { return $this->role_id == Role::where('name', 'dean')->value('id'); }
    public function isRoleInstructor() { return $this->role_id == Role::where('name', 'instructor')->value('id'); }

    public function render()
    {
        return view('livewire.edit-user', [
            'roles'        => $this->roles,
            'colleges'     => $this->colleges,
            'departments'  => $this->departments,
            'yearLevels'   => $this->yearLevels,
            'sections'     => $this->sections,
        ]);
    }
}
