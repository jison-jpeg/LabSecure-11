<?php

namespace App\Livewire;

use App\Models\Laboratory;
use Livewire\Component;

class LabStats extends Component
{
    public $totalLabs;
    public $availableLabs;
    public $occupiedLabs;
    public $types = [];

    public function mount()
    {
        $this->loadLabStats();
    }

    public function loadLabStats()
    {
        // Calculate total laboratories
        $this->totalLabs = Laboratory::count();

        // Calculate available laboratories
        $this->availableLabs = Laboratory::where('status', 'Available')->count();

        // Calculate occupied laboratories
        $this->occupiedLabs = Laboratory::where('status', 'Occupied')->count();

        // Calculate labs by type
        $this->types = Laboratory::select('type')
            ->selectRaw('count(*) as total')
            ->groupBy('type')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->type,
                    'total' => $item->total,
                ];
            })
            ->toArray();
    }

    public function render()
    {
        return view('livewire.lab-stats');
    }
}
