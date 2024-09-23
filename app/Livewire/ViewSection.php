<?php

namespace App\Livewire;

use App\Models\Section;
use App\Models\User;
use App\Models\Schedule;
use Livewire\Component;

class ViewSection extends Component
{
    public $section;
    public $students;
    public $schedules;

    public function mount(Section $section)
    {
        $this->section = $section;
        $this->students = User::where('section_id', $section->id)->get();
        $this->schedules = Schedule::where('section_id', $section->id)->get();
    }

    public function render()
    {
        return view('livewire.view-section');
    }
}
