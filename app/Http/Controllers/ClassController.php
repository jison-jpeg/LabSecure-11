<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassController extends Controller
{
    public function viewClasses()
{
    $user = Auth::user();

    if ($user->isAdmin()) {
        return view('admin.class');
    } elseif ($user->isInstructor()) {
        return view('instructor.class');
    } elseif ($user->isStudent()) {
        return view('student.class');
    } elseif ($user->isDean()) {
        return view('dean.class');
    } elseif ($user->isChairperson()) {
        return view('chairperson.class');
    } else {
        abort(401, 'Unauthorized access.');
    }
}

    // view class method
    public function viewClass(Schedule $schedule)
    {
        $user = Auth::user();

        if ($user->role->name === 'admin') {
            return view('admin.view-class', ['schedule' => $schedule]);
        } elseif ($user->role->name === 'instructor') {
            return view('instructor.view-class', ['schedule' => $schedule]);
        } elseif ($user->role->name === 'student') {
            return view('student.view-class', ['schedule' => $schedule]);
        } else {
            return redirect()->route('unauthorized');
        }
    }

    public function viewSection(Section $section)
    {
        return view('instructor.section', ['section' => $section]);
    }
}
