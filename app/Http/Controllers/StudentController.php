<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function viewStudent()
    {
        return view('admin.student');
    }
}
