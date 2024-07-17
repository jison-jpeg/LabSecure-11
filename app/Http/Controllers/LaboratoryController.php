<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LaboratoryController extends Controller
{
    // View all laboratories
    public function viewLaboratories()
    {
        return view('admin.laboratory');
    }
}
