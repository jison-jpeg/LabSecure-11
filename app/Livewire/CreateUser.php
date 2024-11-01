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
use Illuminate\Validation\Rule;

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
    public $status = 'active';

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
        $this->selectedSection = null;
    }

    public function updatedSelectedDepartment($departmentId)
    {
        $this->sections = Section::where('department_id', $departmentId)->get();
        $this->selectedSection = null;
    }

    public function updatedRoleId()
    {
        if ($this->isRoleAdmin()) {
            // Reset College, Department, and Section for Admin
            $this->reset(['selectedCollege', 'selectedDepartment', 'selectedSection']);
            $this->departments = [];
            $this->sections = [];
        } elseif ($this->isRoleDean()) {
            // Reset Department and Section for Dean
            $this->reset(['selectedDepartment', 'selectedSection']);
            $this->sections = [];
        } else {
            // Reset Department and Section for other roles
            $this->reset(['selectedDepartment', 'selectedSection']);
            if ($this->selectedCollege) {
                // Reload departments based on the current selectedCollege
                $this->departments = Department::where('college_id', $this->selectedCollege)->get();
            } else {
                $this->departments = [];
            }
            $this->sections = [];
        }
    }

    /**
     * Dynamically validate properties based on the selected role.
     */
    public function updated($propertyName)
    {
        $rules = $this->getValidationRules();

        // Validate only the updated property with current rules
        $this->validateOnly($propertyName, $rules);
    }

    /**
     * Retrieve validation rules based on the selected role.
     *
     * @return array
     */
    private function getValidationRules()
    {
        // Base validation rules
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($this->user->id ?? null),
            ],
            'role_id' => 'required|exists:roles,id',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user->id ?? null),
            ],
            'password' => 'nullable|string|min:6',
            'status' => 'required|in:active,inactive',
        ];

        // Additional rules based on role
        if (!$this->isRoleAdmin()) {
            // College is required for Chairperson, Dean, Instructor, and Student
            $rules['selectedCollege'] = [
                'required',
                'exists:colleges,id',
            ];

            if ($this->isRoleChairperson() || $this->isRoleInstructor() || $this->isRoleStudent()) {
                // Department is required for Chairperson, Instructor, and Student
                $rules['selectedDepartment'] = 'required|exists:departments,id';
            }

            if ($this->isRoleStudent()) {
                // Section is required only for Student
                $rules['selectedSection'] = 'required|exists:sections,id';
            }

            // Additional validation for Dean role
            if ($this->isRoleDean()) {
                $rules['selectedCollege'][] = function ($attribute, $value, $fail) {
                    $deanRoleId = Role::where('name', 'dean')->value('id');
                    $query = User::where('role_id', $deanRoleId)
                                 ->where('college_id', $value);

                    // If editing, exclude the current user from the check
                    if ($this->editForm && $this->user) {
                        $query->where('id', '!=', $this->user->id);
                    }

                    if ($query->exists()) {
                        $fail('A dean has already been assigned to this college.');
                    }
                };
            }
        }

        return $rules;
    }

    /**
     * Save a new user with conditional validation.
     */
    public function save()
    {
        $this->validate($this->getValidationRules());

        // Generate a random password if none is provided
        if (empty($this->password)) {
            $generatedPassword = Str::random(10);
        } else {
            $generatedPassword = $this->password;
        }

        // Prepare data for user creation
        $data = [
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'suffix' => $this->suffix,
            'username' => $this->username,
            'role_id' => $this->role_id,
            'email' => $this->email,
            'password' => Hash::make($generatedPassword),
            'status' => $this->status,
        ];

        // Conditionally add college
        if (!$this->isRoleAdmin()) {
            $data['college_id'] = $this->selectedCollege;
        }

        // Conditionally add department
        if ($this->isRoleChairperson() || $this->isRoleInstructor() || $this->isRoleStudent()) {
            $data['department_id'] = $this->selectedDepartment;
        }

        // Conditionally add section for students
        if ($this->isRoleStudent()) {
            $data['section_id'] = $this->selectedSection;
        }

        // Create the user
        $user = User::create($data);

        // Log the transaction
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

        // Send credentials via email
        Mail::to($user->email)->queue(new UserCredentials($user, $generatedPassword));

        // Notify frontend to refresh the user table
        $this->dispatch('refresh-user-table');

        // Show success notification
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('User created successfully');

        // Reset form fields
        $this->resetFields();
    }

    /**
     * Reset all form fields and error messages.
     */
    public function resetFields()
    {
        $this->reset([
            'first_name', 'middle_name', 'last_name', 'suffix', 'username', 'role_id', 'email', 
            'password', 'selectedCollege', 'selectedDepartment', 'selectedSection', 'status'
        ]);
        $this->resetErrorBag();
        $this->departments = [];
        $this->sections = [];
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
        $this->status = $this->user->status;

        // Set and load the college
        if ($this->user->college_id) {
            $this->selectedCollege = $this->user->college_id;
            $this->departments = Department::where('college_id', $this->selectedCollege)->get();
        }

        // Set and load the department if applicable
        if ($this->isRoleChairperson() || $this->isRoleInstructor() || $this->isRoleStudent()) {
            if ($this->user->department_id) {
                $this->selectedDepartment = $this->user->department_id;
                $this->sections = Section::where('department_id', $this->selectedDepartment)->get();
            }
        }

        // Set the section if applicable
        if ($this->isRoleStudent()) {
            if ($this->user->section_id) {
                $this->selectedSection = $this->user->section_id;
            }
        }
    }

    /**
     * Update an existing user with conditional validation.
     */
    public function update()
    {
        $this->validate($this->getValidationRules());

        // Prepare data for user update
        $data = [
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'suffix' => $this->suffix,
            'username' => $this->username,
            'role_id' => $this->role_id,
            'email' => $this->email,
            'status' => $this->status,
        ];

        // Update password if provided
        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        // Conditionally add or remove college
        if (!$this->isRoleAdmin()) {
            $data['college_id'] = $this->selectedCollege;
        } else {
            // If admin, remove college, department, and section
            $data['college_id'] = null;
        }

        // Conditionally add or remove department
        if ($this->isRoleChairperson() || $this->isRoleInstructor() || $this->isRoleStudent()) {
            $data['department_id'] = $this->selectedDepartment;
        } else {
            // For roles that don't require department (e.g., Dean and Admin)
            $data['department_id'] = null;
        }

        // Conditionally add or remove section for students
        if ($this->isRoleStudent()) {
            $data['section_id'] = $this->selectedSection;
        } else {
            $data['section_id'] = null;
        }

        // Update the user
        $this->user->update($data);

        // Log the transaction
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

        // Show success notification
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('User updated successfully');

        // Notify frontend to refresh the user table
        $this->dispatch('refresh-user-table');

        // Reset form fields
        $this->resetFields();
    }

    /**
     * Check if the selected role is Admin.
     *
     * @return bool
     */
    public function isRoleAdmin()
    {
        return $this->role_id && $this->role_id == Role::where('name', 'admin')->value('id');
    }

    /**
     * Check if the selected role is Student.
     *
     * @return bool
     */
    public function isRoleStudent()
    {
        return $this->role_id && $this->role_id == Role::where('name', 'student')->value('id');
    }

    /**
     * Check if the selected role is Chairperson.
     *
     * @return bool
     */
    public function isRoleChairperson()
    {
        return $this->role_id && $this->role_id == Role::where('name', 'chairperson')->value('id');
    }

    /**
     * Check if the selected role is Dean.
     *
     * @return bool
     */
    public function isRoleDean()
    {
        return $this->role_id && $this->role_id == Role::where('name', 'dean')->value('id');
    }

    /**
     * Check if the selected role is Instructor.
     *
     * @return bool
     */
    public function isRoleInstructor()
    {
        return $this->role_id && $this->role_id == Role::where('name', 'instructor')->value('id');
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
