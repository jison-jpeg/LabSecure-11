<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function viewSchedules()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return view('admin.schedule');
        } elseif ($user->isInstructor()) {
            // dd("You are in a schedule page of an instructor");
            return view('instructor.schedule');
        } elseif ($user->isStudent()) {
            return view('student.schedule');
        } elseif ($user->isDean()) {
            return view('dean.schedule'); // Optional for Dean
        } elseif ($user->isChairperson()) {
            return view('chairperson.schedule'); // Optional for Chairperson
        } else {
            abort(401, 'Unauthorized access.');
        }
    }

    public function viewSchedule(Schedule $schedule)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return view('admin.view-schedule', ['schedule' => $schedule]);
        } elseif ($user->isInstructor()) {
            return view('instructor.view-schedule', ['schedule' => $schedule]);
        } elseif ($user->isStudent()) {
            return view('student.view-schedule', ['schedule' => $schedule]);
        } elseif ($user->isDean()) {
            return view('dean.view-schedule', ['schedule' => $schedule]); // Optional for Dean
        } elseif ($user->isChairperson()) {
            return view('chairperson.view-schedule', ['schedule' => $schedule]); // Optional for Chairperson
        } else {
            abort(401, 'Unauthorized access.');
        }
    }
}

