<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function viewStudents()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return view('admin.student');
        } elseif ($user->isInstructor()) {
            return view('instructor.student');
        } elseif ($user->isDean()) {
            return view('dean.student'); // Optional for Dean
        } elseif ($user->isChairperson()) {
            return view('chairperson.student'); // Optional for Chairperson
        } else {
            abort(401, 'Unauthorized access.');
        }
    }

    public function viewStudent(User $student)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return view('admin.view-student', ['student' => $student]);
        } elseif ($user->isInstructor()) {
            return view('instructor.view-student', ['student' => $student]);
        } elseif ($user->isDean()) {
            return view('dean.view-student', ['student' => $student]); // Optional for Dean
        } elseif ($user->isChairperson()) {
            return view('chairperson.view-student', ['student' => $student]); // Optional for Chairperson
        } else {
            abort(401, 'Unauthorized access.');
        }
    }
}

