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

        if ($user->isAdmin()) {
            return view('admin.subject');
        } elseif ($user->isInstructor()) {
            return view('instructor.subject');
        } elseif ($user->isStudent()) {
            return view('student.subject');
        } elseif ($user->isDean()) {
            return view('dean.subject'); // Optional for Dean
        } elseif ($user->isChairperson()) {
            return view('chairperson.subject'); // Optional for Chairperson
        } else {
            abort(401, 'Unauthorized access.');
        }
    }

    public function viewSubject(Subject $subject)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return view('admin.view-subject', ['subject' => $subject]);
        } elseif ($user->isInstructor()) {
            return view('instructor.view-subject', ['subject' => $subject]);
        } elseif ($user->isStudent()) {
            return view('student.view-subject', ['subject' => $subject]);
        } elseif ($user->isDean()) {
            return view('dean.view-subject', ['subject' => $subject]); // Optional for Dean
        } elseif ($user->isChairperson()) {
            return view('chairperson.view-subject', ['subject' => $subject]); // Optional for Chairperson
        } else {
            abort(401, 'Unauthorized access.');
        }
    }
}

