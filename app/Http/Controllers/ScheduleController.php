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
        } elseif ($user->isDean()) {
            return view('dean.schedule');
        } elseif ($user->isChairperson()) {
            return view('chairperson.schedule');
        } else {
            abort(401, 'Unauthorized access.');
        }
    }

    public function viewSchedule(Schedule $schedule)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return view('admin.view-schedule', ['schedule' => $schedule]);
        } elseif ($user->isDean()) {
            return view('dean.view-schedule', ['schedule' => $schedule]);
        } elseif ($user->isChairperson()) {
            return view('chairperson.view-schedule', ['schedule' => $schedule]);
        } else {
            abort(401, 'Unauthorized access.');
        }
    }
}

