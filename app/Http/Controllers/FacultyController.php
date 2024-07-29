<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FacultyController extends Controller
{
    public function viewFaculties(Request $request)
    {
        return view('admin.faculty');
    }
}
