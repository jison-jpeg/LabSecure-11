<?php

namespace App\Http\Controllers;

use App\Events\AttendanceRecorded;
use App\Events\LaboratoryStatusUpdated;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Laboratory;
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
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'type' => 'required|in:entrance,exit', // Added type to handle entrance and exit
        ]);

        // Default the date to today if not provided
        $date = $request->input('date', Carbon::now()->toDateString());

        // Find or create the attendance record for today
        $attendance = Attendance::firstOrCreate([
            'user_id' => $request->user_id,
            'schedule_id' => $request->schedule_id,
            'date' => $date,
        ]);

        // Find the schedule and its laboratory
        $schedule = Schedule::findOrFail($request->schedule_id);
        $laboratory = Laboratory::findOrFail($schedule->laboratory_id);

        switch ($request->type) {
            case 'entrance':
                // Create a new session for the time in
                $attendance->sessions()->create([
                    'time_in' => $request->time_in ? Carbon::parse($request->time_in) : Carbon::now(),
                ]);
                // Set laboratory status to "Occupied"
                $laboratory->update(['status' => 'Occupied']);
                LaboratoryStatusUpdated::dispatch($laboratory);  // Dispatch event
                break;

            case 'exit':
                // Find the most recent session and set the time out
                $lastSession = $attendance->sessions()->whereNull('time_out')->latest('time_in')->first();
                if ($lastSession) {
                    $lastSession->update([
                        'time_out' => $request->time_out ? Carbon::parse($request->time_out) : Carbon::now(),
                    ]);
                }
                // Set laboratory status to "Available"
                $laboratory->update(['status' => 'Available']);
                LaboratoryStatusUpdated::dispatch($laboratory);  // Dispatch event
                break;
        }

        // After the last session of the day, call this method to finalize the status
        $attendance->calculateAndSaveStatusAndRemarks();

        // Dispatch the event for real-time updates
        AttendanceRecorded::dispatch($attendance);

        return response()->json([
            'message' => 'Attendance recorded successfully',
            'data' => $attendance->load('sessions'),
        ], 201);
    }

    // API endpoint to process attendance via RFID
    public function recordAttendance(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'rfid_number' => 'required|string',
            'type' => 'required|in:entrance,exit', // Either 'entrance' or 'exit'
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

        // Find the associated laboratory
        $laboratory = $schedule->laboratory;

        // Find or create the attendance record for today
        $currentDate = Carbon::now()->toDateString();
        $attendance = Attendance::firstOrCreate([
            'user_id' => $user->id,
            'date' => $currentDate,
            'schedule_id' => $schedule->id, // Ensure the schedule is associated with the attendance
        ]);

        switch ($request->type) {
            case 'entrance':
                // Create a new session for the time in
                $attendance->sessions()->create([
                    'time_in' => Carbon::now(),
                ]);
                // Set laboratory status to "Occupied"
                $laboratory->update(['status' => 'Occupied']);
                LaboratoryStatusUpdated::dispatch($laboratory);  // Dispatch event
                break;

            case 'exit':
                // Find the most recent session and set the time out
                $lastSession = $attendance->sessions()->whereNull('time_out')->latest('time_in')->first();
                if ($lastSession) {
                    $lastSession->update(['time_out' => Carbon::now()]);
                }
                // Set laboratory status to "Available"
                $laboratory->update(['status' => 'Available']);
                LaboratoryStatusUpdated::dispatch($laboratory);  // Dispatch event
                break;
        }

        // After the last session of the day, call this method to finalize the status
        $attendance->calculateAndSaveStatusAndRemarks();

        // Dispatch the event for real-time updates
        AttendanceRecorded::dispatch($attendance);

        return response()->json([
            'message' => 'Attendance recorded successfully',
            'data' => $attendance->load('sessions'),
        ]);
    }
}
