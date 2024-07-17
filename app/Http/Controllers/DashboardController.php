<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function redirectToDashboard()
    {
        $user = Auth::user();

        if ($user->role->name === 'admin') {
            return view('admin.dashboard');
        } elseif ($user->role->name === 'instructor') {
            return view('instructor.dashboard');
        } elseif ($user->role->name === 'student') {
            return view('student.dashboard');
        } else {
            return redirect()->route('unauthorized');
        }
    }
}
