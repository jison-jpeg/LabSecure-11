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
        
        // Load year levels based on the selected department
        if ($this->selectedDepartment) {
            $this->yearLevels = Section::where('department_id', $this->selectedDepartment)
                                       ->pluck('year_level')
                                       ->unique()
                                       ->sort()
                                       ->values()
                                       ->toArray();
        }

        // Set the selected year level based on the user's section
        $this->selectedYearLevel = $this->user->section ? $this->user->section->year_level : null;

        // If a year level is selected, load the corresponding sections
        if ($this->selectedYearLevel) {
            $this->sections = Section::where('department_id', $this->selectedDepartment)
                                     ->where('year_level', $this->selectedYearLevel)
                                     ->get();
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
            'college_id'    => $this->selectedCollege,
            'department_id' => $this->selectedDepartment,
            'section_id'    => $this->selectedSection,
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

        notyf()->position('x', 'right')->position('y', 'top')->success('User updated successfully');

        
        $this->dispatch('close-modal');
    }

    public function close()
    {
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
