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

    // View attendance for a specific user
    public function viewUserAttendance(User $user)
    {
        return view('admin.view-attendance', ['user' => $user]);
    }

}
