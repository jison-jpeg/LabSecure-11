<?php

namespace App\Livewire;

use App\Models\TransactionLog;
use Carbon\Carbon;
use Livewire\Component;

class LogsWidget extends Component
{
    public $filter = 'today';  // Default filter to 'today'

    // Computed property to get filtered logs
    public function getLogsProperty()
    {
        $query = TransactionLog::with('user')->latest();

        switch ($this->filter) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;

            case 'month':
                $query->whereMonth('created_at', Carbon::now()->month);
                break;

            case 'year':
                $query->whereYear('created_at', Carbon::now()->year);
                break;
        }

        return $query->take(11)->get();  // Limit to 10 logs for the widget
    }

    public function render()
    {
        return view('livewire.logs-widget');
    }
}
