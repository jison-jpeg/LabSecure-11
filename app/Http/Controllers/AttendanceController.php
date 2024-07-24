<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Attendance extends Controller
{
    // View all attendance
    public function viewAttendance(Request $request)
    {
        return view('admin.attendance');
    }
}
