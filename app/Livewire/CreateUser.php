<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\Attributes\On;
use Flasher\Notyf\Prime\NotyfInterface;

class CreateUser extends Component
{
    public $user;
    public $formTitle = 'Create User';
    public $editForm = false;
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
            'password' => 'required|min:6',
        ]);

        User::create([
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'role_id' => $this->role_id,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        
        $this->dispatch('refresh-user-table');
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('User created successfully');
        $this->reset();
    }

    #[On('reset-modal')]
    public function close(){
        $this->reset();
    }

    #[On('edit-mode')]
    public function edit($id)
    {
        $this->editForm = true;
        $this->formTitle = 'Edit User';
        $this->user = User::find($id);
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
        ]);

        $this->user->update([
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'role_id' => $this->role_id,
            'email' => $this->email,
        ]);

        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('User updated successfully');
        $this->dispatch('refresh-user-table');
        $this->reset();
    }




}
