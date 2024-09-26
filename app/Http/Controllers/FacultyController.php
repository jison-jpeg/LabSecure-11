<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    public function viewFaculties(Request $request)
    {
        return view('admin.faculty');
    }

    public function viewFaculty(User $faculty)
    {
        return view('admin.view-faculty', compact('faculty'));
    }
}
