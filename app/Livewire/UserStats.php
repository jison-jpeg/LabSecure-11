<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;

class UserStats extends Component
{
    public $usersCount;
    public $instructorsCount;
    public $studentsCount;

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $this->usersCount = User::count(); // Count all users
        $this->instructorsCount = User::whereHas('role', function($query) {
            $query->where('name', 'instructor');
        })->count(); // Count instructors

        $this->studentsCount = User::whereHas('role', function($query) {
            $query->where('name', 'student');
        })->count(); // Count students
    }

    public function render()
    {
        return view('livewire.user-stats');
    }
}
