<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    // View all attendance
    public function viewAttendance(Request $request)
    {
        $user = Auth::user();

        if ($user->role->name === 'admin') {
            return view('admin.attendance');
        } elseif ($user->role->name === 'instructor') {
            return view('instructor.attendance');
        } elseif ($user->role->name === 'student') {
            return view('student.attendance');
        } else {
            return redirect()->route('unauthorized');
        }
    }

    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'schedule_id' => 'required|exists:schedules,id',
            'date' => 'required|date',
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
        ]);

        // Create a new attendance record
        $attendance = Attendance::create([
            'user_id' => $request->user_id,
            'schedule_id' => $request->schedule_id,
            'date' => $request->date,
            'time_in' => $request->time_in,
            'time_out' => $request->time_out,
        ]);

        // Calculate and save the status and remarks
        $attendance->calculateAndSaveStatusAndRemarks();

        return response()->json([
            'message' => 'Attendance record created successfully',
            'data' => $attendance,
        ], 201);
    }
}
