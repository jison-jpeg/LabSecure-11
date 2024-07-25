<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CollegeManagementController extends Controller
{
    public function viewCollegeManagement()
    {
        return view('admin.college-management');
    }
}
