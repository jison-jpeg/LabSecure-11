<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassController extends Controller
{
    public function viewClasses()
    {
        $user = Auth::user();

        if ($user->role->name === 'admin') {
            return view('admin.class');
        } elseif ($user->role->name === 'instructor') {
            return view('instructor.class');
        } elseif ($user->role->name === 'student') {
            return view('student.class');
        } else {
            return redirect()->route('unauthorized');
        }
    }

    public function viewSection(Section $section)
    {
        return view('instructor.section', ['section' => $section]);
    }
}
