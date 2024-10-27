<?php

namespace App\Livewire;

use App\Models\College;
use App\Models\Role;
use Livewire\Component;

class ViewCollege extends Component
{
    public $college;

    public function mount(College $college)
{
    $this->college = $college->load('dean');
}


    public function render()
    {
        return view('livewire.view-college', [
            'college' => $this->college,
            'studentsCount' => $this->college->users()->where('role_id', Role::where('name', 'student')->first()->id)->count(),
            'instructorsCount' => $this->college->users()->where('role_id', Role::where('name', 'instructor')->first()->id)->count(),
            'departmentsCount' => $this->college->departments->count(),
            'departments' => $this->college->departments,
        ]);
    }
}
