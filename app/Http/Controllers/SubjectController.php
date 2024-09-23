<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubjectController extends Controller
{
    public function viewSubjects()
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

    public function viewSubject(Subject $subject)
    {
        $user = Auth::user();

        if ($user->role->name === 'admin') {
            return view('admin.view-subject', ['subject' => $subject]);
        } elseif ($user->role->name === 'instructor') {
            return view('instructor.view-subject', ['subject' => $subject]);
        } elseif ($user->role->name === 'student') {
            return view('student.view-subject', ['subject' => $subject]);
        } else {
            return redirect()->route('unauthorized');
        }
    }
}
