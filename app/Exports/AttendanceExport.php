<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class AttendanceExport implements FromQuery, WithHeadings, WithMapping, WithEvents
{
    protected $selectedMonth;
    protected $selectedSubject;
    protected $selectedSection;

    public function __construct($selectedMonth, $selectedSubject, $selectedSection)
    {
        $this->selectedMonth = $selectedMonth;
        $this->selectedSubject = $selectedSubject;
        $this->selectedSection = $selectedSection;
    }

    public function query()
    {
        return Attendance::with(['user', 'schedule.subject', 'schedule.section', 'schedule.laboratory', 'sessions'])
            ->when($this->selectedMonth, function ($query) {
                $query->whereMonth('date', Carbon::parse($this->selectedMonth)->month)
                    ->whereYear('date', Carbon::parse($this->selectedMonth)->year);
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
            });
    }

    public function headings(): array
    {
        return [
            'Date',
            'Name',
            'Role',
            'Section Code',
            'Section',
            'Subject',
            'Schedule',
            'Laboratory',
            'Time In',
            'Time Out',
            'Status',
            'Remarks'
        ];
    }

    public function map($attendance): array
    {
        $schedule = $attendance->schedule;

        // Format the schedule time (e.g., "7:30 AM - 8:30 AM")
        $scheduleTime = Carbon::parse($schedule->start_time)->format('h:i A') . ' - ' . Carbon::parse($schedule->end_time)->format('h:i A');

        // Get shortened days of the week
        $shortDaysOfWeek = $this->getShortenedDays(json_decode($schedule->days_of_week));

        // Combine the time and shortened days (e.g., "7:30 AM - 8:30 AM (Mon, Tue)")
        $formattedSchedule = "{$scheduleTime} ({$shortDaysOfWeek})";

        return [
            Carbon::parse($attendance->date)->format('m/d/Y'), // Date (first column)
            $attendance->user->full_name,  // Full name of the user
            $attendance->user->role->name, // Role of the user (e.g., student/instructor)
            $schedule->schedule_code,      // Section code
            optional($schedule->section)->name, // Section name
            optional($schedule->subject)->name, // Subject name
            $formattedSchedule,            // Schedule (time and shortened days of week)
            optional($schedule->laboratory)->name, // Laboratory name
            $attendance->formattedTimeIn,  // Time In from accessor
            $attendance->formattedTimeOut, // Time Out from accessor
            $attendance->status,           // Status (Present, Late, Absent)
            $attendance->remarks,          // Remarks
        ];
    }

    // Helper function to get shortened days of the week
    protected function getShortenedDays($days)
    {
        $shortDays = [
            'Monday' => 'Mon',
            'Tuesday' => 'Tue',
            'Wednesday' => 'Wed',
            'Thursday' => 'Thu',
            'Friday' => 'Fri',
            'Saturday' => 'Sat',
            'Sunday' => 'Sun',
        ];

        return implode(', ', array_map(function ($day) use ($shortDays) {
            return $shortDays[$day] ?? $day;
        }, $days));
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Style the header row with blue background
                $headerStyle = [
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFF'], // White text color
                    ],
                    'alignment' => ['horizontal' => 'left'], // Left-align the header text
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF4F81BD'], // Dark blue background
                    ]
                ];

                // Apply the header style
                $event->sheet->getStyle('A1:L1')->applyFromArray($headerStyle);

                // Apply left alignment to all cells in the data rows
                $contentStyle = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, // Left alignment for all content
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ];

                // Apply the content style to the entire data range (A2 to the last row)
                $highestRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle('A2:L' . $highestRow)->applyFromArray($contentStyle);

                // Auto-size the columns
                foreach (range('A', 'L') as $columnID) {
                    $event->sheet->getColumnDimension($columnID)->setAutoSize(true);
                }
            },
        ];
    }
}
