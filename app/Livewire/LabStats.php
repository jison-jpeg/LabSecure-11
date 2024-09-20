<?php

namespace App\Livewire;

use App\Models\Laboratory;
use Livewire\Component;

class LabStats extends Component
{
    public $totalLabs;
    public $availableLabs;
    public $types = [];

    public function mount()
    {
        $this->loadLabStats();
    }

    public function loadLabStats()
    {
        // Calculate total laboratories
        $this->totalLabs = Laboratory::count();

        // Calculate labs by type and their availability
        $this->types = Laboratory::select('type')
            ->selectRaw('count(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'Available' THEN 1 ELSE 0 END) as available")
            ->groupBy('type')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->type,
                    'total' => $item->total,
                    'available' => $item->available,  // Available labs of this type
                    'percentage' => ($item->total > 0) ? ($item->available / $item->total) * 100 : 0,  // Calculate percentage
                ];
            })
            ->toArray();
    }

    public function render()
    {
        return view('livewire.lab-stats');
    }
}
