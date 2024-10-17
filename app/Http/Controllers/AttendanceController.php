<?php

namespace App\Http\Controllers;

use App\Events\AttendanceRecorded;
use App\Events\LaboratoryStatusUpdated;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Laboratory;
use App\Models\TransactionLog; // Include TransactionLog model
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    // View all attendance
    public function viewAttendance(Request $request)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return view('admin.attendance');
        } elseif ($user->isInstructor()) {
            return view('instructor.attendance');
        } elseif ($user->isStudent()) {
            return view('student.attendance');
        } elseif ($user->isDean()) {
            return view('dean.attendance');
        } elseif ($user->isChairperson()) {
            return view('chairperson.attendance');
        } else {
            abort(401, message: 'Unauthorized access.');
        }
    }

    // View attendance for a specific subject for instructor
    public function viewSubjectAttendance(Schedule $schedule)
    {
        // Ensure the instructor has access to this schedule
        $user = Auth::user();

        if ($user->role->name === 'instructor' && $schedule->instructor_id != $user->id) {
            return redirect()->route('unauthorized');
        }

        $attendances = Attendance::where('schedule_id', $schedule->id)->paginate(10);

        return view('instructor.view-subject-attendance', [
            'schedule' => $schedule,
            'attendances' => $attendances,
        ]);
    }

    // View attendance for a specific user
    public function viewUserAttendance(User $user)
    {
        return view('admin.view-attendance', ['user' => $user]);
    }
}
