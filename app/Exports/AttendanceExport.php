<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class AttendanceExport implements FromQuery, WithHeadings, WithMapping
{
    protected $search;
    protected $status;
    protected $selectedMonth;
    protected $selectedSubject;
    protected $selectedSection;
    protected $rowNumber = 0; // Initialize a row counter

    public function __construct($search, $status, $selectedMonth, $selectedSubject, $selectedSection)
    {
        $this->search = $search;
        $this->status = $status;
        $this->selectedMonth = $selectedMonth;
        $this->selectedSubject = $selectedSubject;
        $this->selectedSection = $selectedSection;
    }

    public function query()
    {
        $query = Attendance::with(['user', 'schedule.subject', 'schedule.section']);

        // Apply search filters
        $query->whereHas('user', function ($query) {
            $query->where('first_name', 'like', '%' . $this->search . '%')
                ->orWhere('last_name', 'like', '%' . $this->search . '%');
        });

        // Apply status filter
        if (!empty($this->status)) {
            $query->where('status', $this->status);
        }

        // Apply subject filter
        if (!empty($this->selectedSubject)) {
            $query->whereHas('schedule.subject', function ($query) {
                $query->where('id', $this->selectedSubject);
            });
        }

        // Apply section filter
        if (!empty($this->selectedSection)) {
            $query->whereHas('schedule.section', function ($query) {
                $query->where('id', $this->selectedSection);
            });
        }

        // Apply month filter
        if ($this->selectedMonth) {
            $query->whereMonth('date', Carbon::parse($this->selectedMonth)->month)
                ->whereYear('date', Carbon::parse($this->selectedMonth)->year);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            '#',
            'Username',
            'Last Name',
            'First Name',
            'Middle Name',
            'Subject',
            'Section',
            'Status',
            'Date',
        ];
    }

    public function map($attendance): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $attendance->user->username,
            $attendance->user->last_name,
            $attendance->user->first_name,
            $attendance->user->middle_name,
            $attendance->schedule->subject->name,
            $attendance->schedule->section->name,
            $attendance->status,
            $attendance->date,
        ];
    }
}
