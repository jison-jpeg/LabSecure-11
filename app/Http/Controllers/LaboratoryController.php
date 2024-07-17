<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use Illuminate\Http\Request;

class LaboratoryController extends Controller
{
    // View all laboratories
    public function viewLaboratories()
    {
        $laboratories = Laboratory::paginate(2); // Adjust the pagination limit as needed
        return view('admin.laboratory', compact('laboratories'));
    }
}
