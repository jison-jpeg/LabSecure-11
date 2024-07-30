<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
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
}
