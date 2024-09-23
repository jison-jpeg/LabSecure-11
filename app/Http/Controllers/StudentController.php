<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function viewStudents()
    {
        return view('admin.student');
    }

    public function viewStudent(User $student)
{
    return view('admin.view-student', ['student' => $student]);
}

}
