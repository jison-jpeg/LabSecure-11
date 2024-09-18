<?php

namespace App\Livewire;

use App\Models\Laboratory;
use Livewire\Component;

class ViewLaboratory extends Component
{
    public $laboratory;

    public function mount(Laboratory $laboratory)
    {
        // The $laboratory object will be passed directly
        $this->laboratory = $laboratory;
    }

    public function render()
    {
        return view('livewire.view-laboratory', [
            'laboratory' => $this->laboratory,
        ]);
    }
}
