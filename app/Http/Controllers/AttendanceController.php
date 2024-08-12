<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
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

    // API endpoint to process attendance via RFID
    public function recordAttendance(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'rfid_number' => 'required|string',
        ]);

        // Find the user by RFID
        $user = User::where('rfid_number', $request->rfid_number)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Find the current schedule for the user based on the current time
        $currentTime = Carbon::now();
        $schedule = Schedule::where('start_time', '<=', $currentTime)
                            ->where('end_time', '>=', $currentTime)
                            ->first();

        if (!$schedule) {
            return response()->json(['message' => 'No active schedule found for this time'], 404);
        }

        // Check if an attendance record already exists for this user and schedule
        $attendance = Attendance::where('user_id', $user->id)
                                ->where('schedule_id', $schedule->id)
                                ->where('date', $currentTime->toDateString())
                                ->first();

        if (!$attendance) {
            // Create a new attendance record with the time_in
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'schedule_id' => $schedule->id,
                'date' => $currentTime->toDateString(),
                'time_in' => $currentTime,
            ]);
        } else {
            // Update the time_out if time_in already exists
            if (!$attendance->time_out) {
                $attendance->time_out = $currentTime;
            }
        }

        // Calculate and save status and remarks based on the new data
        $attendance->calculateAndSaveStatusAndRemarks();

        return response()->json([
            'message' => 'Attendance recorded successfully',
            'data' => $attendance,
        ]);
    }
}
