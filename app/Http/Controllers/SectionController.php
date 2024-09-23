<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function viewSections()
    {
        return view('admin.section');
    }

    public function viewSection(Section $section)
    {
        return view('admin.view-section', ['section' => $section]);
    }
}
