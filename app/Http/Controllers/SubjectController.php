<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function viewSubject()
    {
        return view('admin.subject');
    }
}
