<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubjectController extends Controller
{
    public function viewSubject()
    {
        $user = Auth::user();

        if ($user->role->name === 'admin') {
            return view('admin.subject');
        } elseif ($user->role->name === 'instructor') {
            return view('instructor.subject');
        } elseif ($user->role->name === 'student') {
            return view('student.subject');
        } else {
            return redirect()->route('unauthorized');
        }
    }
}