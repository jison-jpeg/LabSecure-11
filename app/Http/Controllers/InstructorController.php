<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InstructorController extends Controller
{
    public function viewDashboard()
    {
        return view('instructor.dashboard');
    }
}
