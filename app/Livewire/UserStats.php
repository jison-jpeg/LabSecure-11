<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
        $user = Auth::user();

        if ($user->isAdmin()) {
            // Admin: View all users
            $this->usersCount = User::count();
            $this->instructorsCount = User::whereHas('role', function($query) {
                $query->where('name', 'instructor');
            })->count();
            $this->studentsCount = User::whereHas('role', function($query) {
                $query->where('name', 'student');
            })->count();
        } elseif ($user->isDean() || $user->isChairperson()) {
            // Dean or Chairperson: View users from their own college only
            $collegeId = $user->college_id;

            $this->usersCount = User::where('college_id', $collegeId)->count();
            $this->instructorsCount = User::where('college_id', $collegeId)
                ->whereHas('role', function($query) {
                    $query->where('name', 'instructor');
                })->count();
            $this->studentsCount = User::where('college_id', $collegeId)
                ->whereHas('role', function($query) {
                    $query->where('name', 'student');
                })->count();
        } else {
            // For other roles, set counts to zero (or handle as needed)
            $this->usersCount = 0;
            $this->instructorsCount = 0;
            $this->studentsCount = 0;
        }
    }

    public function render()
    {
        return view('livewire.user-stats');
    }
}
