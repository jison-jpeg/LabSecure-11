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
            ['Subject: ' . $this->sectionName . ' - ' . $this->subjectName . ' : ' . $this->scheduleDays . ' : ' . $this->scheduleTime], // Subject and schedule details
            ['Days', '', 'Time In', 'Time Out', 'Status', 'Remarks'], // Column headers
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
                $attendanceForDay ? $attendanceForDay->formatted_time_in : '-', // Time In (use accessor)
                $attendanceForDay ? $attendanceForDay->formatted_time_out : '-', // Time Out (use accessor)
                $attendanceForDay ? $attendanceForDay->status : '-', // Status
                $attendanceForDay ? $attendanceForDay->remarks : '-', // Remarks
            ];
        }

        return collect($rows);
    }

    public function map($row): array
    {
        return [
            $row[0], // Day number
            $row[1], // Weekday
            $row[2], // Time In
            $row[3], // Time Out
            $row[4], // Status
            $row[5], // Remarks
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
                $event->sheet->getStyle('A1:F4')->getFont()->setBold(true);
            },
        ];
    }
}
