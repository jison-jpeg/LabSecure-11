<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Subject;

class ViewSubject extends Component
{
    public $subject;

    // Initialize the component with the subject model
    public function mount(Subject $subject)
    {
        $this->subject = $subject;
    }

    public function render()
    {
        return view('livewire.view-subject', [
            'subject' => $this->subject,
            'schedules' => $this->subject->schedules()->with('instructor', 'section')->get()
        ]);
    }
}
