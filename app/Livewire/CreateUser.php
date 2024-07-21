<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class CreateUser extends Component
{
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

        return redirect()->to('/users');
    }
}
