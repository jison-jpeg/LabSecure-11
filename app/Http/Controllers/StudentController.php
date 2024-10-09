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
        if ($user->role->name === 'admin') {
            return view('admin.student');
        } elseif ($user->role->name === 'instructor') {
            return view('instructor.student');
        } else {
            abort(401, message: 'Unauthorized access.');
        }
    }

    public function viewStudent(User $student)
    {
        $user = Auth::user();
        if ($user->role->name === 'admin') {
            return view('admin.view-student', ['student' => $student]);
        } elseif ($user->role->name === 'instructor') {
            return view('instructor.view-student', ['student' => $student]);
        } else {
            abort(401, message: 'Unauthorized access.');
        }
    }
}
