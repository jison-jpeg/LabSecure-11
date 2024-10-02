<?php

namespace App\Livewire;

use App\Mail\UserCredentials;
use App\Models\TransactionLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Str;
use Flasher\Notyf\Prime\NotyfInterface;
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
    public $username;
    public $role_id;
    public $email;
    public $password;
    
    public function render()
    {
        return view('livewire.create-user');
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, [
            'first_name' => 'required',
            'last_name' => 'required',
            'username' => 'required|unique:users',
            'role_id' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
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
            'username' => $this->username,
            'role_id' => $this->role_id,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        // Log creation of user
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
        $this->formTitle = 'Edit User';
        $this->editForm = true;
        $this->user = User::findOrFail($id);
        $this->first_name = $this->user->first_name;
        $this->middle_name = $this->user->middle_name;
        $this->last_name = $this->user->last_name;
        $this->username = $this->user->username;
        $this->role_id = $this->user->role_id;
        $this->email = $this->user->email;
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
            'username' => $this->username,
            'role_id' => $this->role_id,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        // Log update of user
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
        $this->reset();
    }
}
