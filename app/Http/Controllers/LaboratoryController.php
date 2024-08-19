<?php

namespace App\Http\Controllers;

use App\Events\AttendanceRecorded;
use App\Events\LaboratoryStatusUpdated;
use App\Models\Attendance;
use App\Models\Laboratory;
use App\Models\Schedule;
use App\Models\TransactionLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaboratoryController extends Controller
{
    // View all laboratories
    public function viewLaboratories()
    {
        $user = Auth::user();
        if ($user->role->name === 'admin') {
            $laboratories = Laboratory::paginate(2);

            return view('admin.laboratory', compact('laboratories'));
        } elseif ($user->role->name === 'instructor') {

            return view('instructor.laboratory');
        } else {
            return redirect()->route('unauthorized');
        }
    }

    // API endpoint to handle laboratory access via RFID
    public function handleLaboratoryAccess(Request $request)
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

        // Check if the user is Admin, IT Support, or a role that can access without a schedule
        if (in_array($user->role->name, ['admin', 'it_support'])) {
            return $this->handlePersonnelAccess($user, $request->type);
        }

        // Handle regular users with schedule
        return $this->handleScheduledUserAccess($user, $request->type);
    }

    // Handle access for personnel without schedules (Admin, IT Support, etc.)
    private function handlePersonnelAccess($user, $type)
    {
        // Find the laboratory associated with the user's access
        $laboratory = Laboratory::where('id', 1)->first();  // Adjust logic to find the correct laboratory

        switch ($type) {
            case 'entrance':
                // Log the entrance action for personnel
                TransactionLog::create([
                    'user_id' => $user->id,
                    'action' => 'check_in',
                    'model' => 'Laboratory',
                    'model_id' => $laboratory->id,
                    'details' => json_encode(['rfid_number' => $user->rfid_number, 'laboratory_status' => 'Occupied']),
                ]);
                LaboratoryStatusUpdated::dispatch($laboratory);  // Dispatch event
                break;

            case 'exit':
                // Log the exit action for personnel
                TransactionLog::create([
                    'user_id' => $user->id,
                    'action' => 'check_out',
                    'model' => 'Laboratory',
                    'model_id' => $laboratory->id,
                    'details' => json_encode(['rfid_number' => $user->rfid_number, 'laboratory_status' => 'Available']),
                ]);
                LaboratoryStatusUpdated::dispatch($laboratory);  // Dispatch event
                break;
        }

        return response()->json([
            'message' => 'Access granted.',
            'laboratory' => $laboratory,
        ], 200);
    }

    // Handle access for users with active schedules
    private function handleScheduledUserAccess($user, $type)
    {
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

        switch ($type) {
            case 'entrance':
                // Create a new session for the time in
                $attendance->sessions()->create([
                    'time_in' => Carbon::now(),
                ]);
                // Set laboratory status to "Occupied"
                $laboratory->update(['status' => 'Occupied']);
                LaboratoryStatusUpdated::dispatch($laboratory);  // Dispatch event

                // Log the entrance action
                TransactionLog::create([
                    'user_id' => $user->id,
                    'action' => 'check_in',
                    'model' => 'Attendance',
                    'model_id' => $attendance->id,
                    'details' => json_encode(['rfid_number' => $user->rfid_number, 'laboratory_status' => 'Occupied']),
                ]);
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

                // Log the exit action
                TransactionLog::create([
                    'user_id' => $user->id,
                    'action' => 'check_out',
                    'model' => 'Attendance',
                    'model_id' => $attendance->id,
                    'details' => json_encode(['rfid_number' => $user->rfid_number, 'laboratory_status' => 'Available']),
                ]);
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
