<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollegeManagementController extends Controller
{
    public function viewCollegeManagement()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return view('admin.college-management');
        } elseif ($user->isInstructor()) {
            return view('instructor.college-management');
        } elseif ($user->isDean()) {
            return view('dean.college-management'); // Optional for Dean
        } elseif ($user->isChairperson()) {
            return view('chairperson.college-management'); // Optional for Chairperson
        } else {
            abort(401, 'Unauthorized access.');
        }
    }

    // View specific college
    public function viewCollege(College $college)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return view('admin.view-college', ['college' => $college]);
        } elseif ($user->isInstructor()) {
            return view('instructor.view-college', ['college' => $college]);
        } elseif ($user->isDean()) {
            return view('dean.view-college', ['college' => $college]); // Optional for Dean
        } elseif ($user->isChairperson()) {
            return view('chairperson.view-college', ['college' => $college]); // Optional for Chairperson
        } else {
            abort(401, 'Unauthorized access.');
        }
    }

    // View specific department
    public function viewDepartment(Department $department)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return view('admin.view-department', ['department' => $department]);
        } elseif ($user->isInstructor()) {
            return view('instructor.view-department', ['department' => $department]);
        } elseif ($user->isDean()) {
            return view('dean.view-department', ['department' => $department]); // Optional for Dean
        } elseif ($user->isChairperson()) {
            return view('chairperson.view-department', ['department' => $department]); // Optional for Chairperson
        } else {
            abort(401, 'Unauthorized access.');
        }
    }
}
