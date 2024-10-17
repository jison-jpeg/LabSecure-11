<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function redirectToDashboard()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return view('admin.dashboard');
        } elseif ($user->isInstructor()) {
            return view('instructor.dashboard');
        } elseif ($user->isStudent()) {
            return view('student.dashboard');
        } elseif ($user->isDean()) {
            return view('dean.dashboard');
        } elseif ($user->isChairperson()) {
            return view('chairperson.dashboard');
        } else {
            abort(401, message: 'Unauthorized access.');
        }
    }
}
