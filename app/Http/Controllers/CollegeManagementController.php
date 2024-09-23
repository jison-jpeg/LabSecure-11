<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\Department;
use Illuminate\Http\Request;

class CollegeManagementController extends Controller
{
    public function viewCollegeManagement()
    {
        return view('admin.college-management');
    }

    // view specific college
    public function viewCollege(College $college)
    {
        return view('admin.view-college', ['college' => $college]);
    }

    // view specific department
    public function viewDepartment(Department $department)
    {
        return view('admin.view-department', ['department' => $department]);
    }
}
