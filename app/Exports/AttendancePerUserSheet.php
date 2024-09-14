<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class AttendancePerUserSheet implements FromCollection, WithHeadings, WithTitle, WithEvents
{
    protected $fullName;
    protected $roleName;
    protected $subjectName;
    protected $sectionName;
    protected $scheduleDays;
    protected $scheduleTime;
    protected $selectedMonth;
    protected $attendances;

    public function __construct($fullName, $roleName, $subjectName, $sectionName, $scheduleDays, $scheduleTime, $selectedMonth, $attendances)
    {
        $this->fullName = $fullName;
        $this->roleName = $roleName; // Role of the user (position)
        $this->subjectName = $subjectName;
        $this->sectionName = $sectionName;
        $this->scheduleDays = $scheduleDays; // e.g., "Mon - Fri"
        $this->scheduleTime = $scheduleTime; // e.g., "8:00 AM - 10:00 AM"
        $this->selectedMonth = $selectedMonth;
        $this->attendances = $attendances;
    }

    public function headings(): array
    {
        return [
            ['Name: ' . $this->fullName, 'Position: ' . $this->roleName], // Full name and position (role name)
            ['Month: ' . Carbon::parse($this->selectedMonth)->format('F Y')],
            ['Schedule: ' . $this->sectionName . ' - ' . $this->subjectName . ' : ' . $this->scheduleDays . ' : ' . $this->scheduleTime], // Subject and schedule details
            ['Days', '', 'Status', 'Time In', 'Time Out'], // Column headers with merged cells for 'Days'
        ];
    }

    public function collection()
    {
        $daysInMonth = Carbon::parse($this->selectedMonth)->daysInMonth;

        $rows = [];

        // Loop through each day of the selected month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDate = Carbon::parse("{$this->selectedMonth}-{$day}");
            $weekday = $currentDate->format('D');  // Day of the week (Mon, Tue, etc.)

            // Find attendance for this day
            $attendanceForDay = $this->attendances->firstWhere('date', $currentDate->toDateString());

            $rows[] = [
                $day, // Day number
                $weekday, // Weekday
                $attendanceForDay ? $attendanceForDay->status : '-', // Status
                $attendanceForDay && $attendanceForDay->sessions->first() ? Carbon::parse($attendanceForDay->sessions->first()->time_in)->format('h:i A') : '-', // Time In
                $attendanceForDay && $attendanceForDay->sessions->first() ? Carbon::parse($attendanceForDay->sessions->first()->time_out)->format('h:i A') : '-', // Time Out
            ];
        }

        return collect($rows);
    }

    public function map($row): array
    {
        return [
            $row[0], // Day number
            $row[1], // Weekday
            $row[2], // Status
            $row[3], // Time In
            $row[4], // Time Out
        ];
    }

    public function title(): string
    {
        return "{$this->fullName} - {$this->subjectName}";
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Merge the 'Days' header over two columns (A4 and B4)
                $event->sheet->mergeCells('A4:B4'); // Merge 'Days' over two cells

                // Optionally, set bold for the headers
                $event->sheet->getStyle('A1:E4')->getFont()->setBold(true);
            },
        ];
    }
}
