<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function viewSchedules()
    {
        $user = Auth::user();

        if ($user->role->name === 'admin') {
            return view('admin.schedule');
        } elseif ($user->role->name === 'instructor') {
            // dd("You are in a schedule page of an instructor");
            return view('instructor.schedule');
        } elseif ($user->role->name === 'student') {
            return view('student.schedule');
        } else {
            return redirect()->route('unauthorized');
        }
    }

    public function viewSchedule($schedule)
    {
        $user = Auth::user();

        if ($user->role->name === 'admin') {
            return view('admin.view-schedule', ['schedule' => $schedule]);
        } elseif ($user->role->name === 'instructor') {
            return view('instructor.view-schedule', ['schedule' => $schedule]);
        } elseif ($user->role->name === 'student') {
            return view('student.view-schedule', ['schedule' => $schedule]);
        } else {
            return redirect()->route('unauthorized');
        }
    }
}
