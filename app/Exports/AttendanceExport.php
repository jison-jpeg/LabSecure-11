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
        return Attendance::with(['user', 'schedule.subject', 'schedule.section', 'sessions']) // Eager-load sessions
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($query) {
                    $query->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->selectedSubject, function ($query) {
                $query->whereHas('schedule.subject', function ($query) {
                    $query->where('id', $this->selectedSubject);
                });
            })
            ->when($this->selectedSection, function ($query) {
                $query->whereHas('schedule.section', function ($query) {
                    $query->where('id', $this->selectedSection);
                });
            })
            ->when($this->selectedMonth, function ($query) {
                $query->whereMonth('date', Carbon::parse($this->selectedMonth)->month)
                    ->whereYear('date', Carbon::parse($this->selectedMonth)->year);
            });
    }


    public function headings(): array
    {
        return [
            '#',
            'Date',
            'Username',
            'Last Name',
            'First Name',
            'Middle Name',
            'Subject',
            'Section',
            'Time In',
            'Time Out',
            'Status',
        ];
    }

    public function map($attendance): array
    {
        $this->rowNumber++;
        $timeIn = optional($attendance->sessions->first())->time_in
            ? Carbon::parse($attendance->sessions->first()->time_in)->format('h:i A')
            : null; 

        $timeOut = optional($attendance->sessions->first())->time_out
            ? Carbon::parse($attendance->sessions->first()->time_out)->format('h:i A')
            : null;

        return [
            $this->rowNumber,
            $attendance->date,
            $attendance->user->username,
            $attendance->user->last_name,
            $attendance->user->first_name,
            $attendance->user->middle_name,
            $attendance->schedule->subject->name,
            $attendance->schedule->section->name,
            $timeIn,
            $timeOut,
            $attendance->status,
        ];
    }
}
