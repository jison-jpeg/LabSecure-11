<?php

namespace App\Livewire;

use App\Models\TransactionLog;
use App\Models\Section;
use Carbon\Carbon;
use Livewire\Component;

class LogsWidget extends Component
{
    public $filter = 'today';  // Default filter to 'today'
    public $collegeId;  // The ID of the selected college

    // Computed property to get filtered logs
    public function getLogsProperty()
    {
        $query = TransactionLog::with('user')->latest();

        // Filter based on the collegeId
        if ($this->collegeId) {
            // Get all section IDs related to the selected college
            $sectionIds = Section::where('college_id', $this->collegeId)->pluck('id');

            // Filter logs where the model is 'Section' and the model_id is one of the section IDs
            $query->where(function($q) use ($sectionIds) {
                $q->where('model', 'Section')
                  ->whereIn('model_id', $sectionIds);
            });
        }

        // Additional filters for today, month, or year
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
