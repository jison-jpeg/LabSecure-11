<?php

namespace App\Exports;

use App\Models\Schedule;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class ScheduleExport implements FromQuery, WithHeadings, WithMapping, WithEvents
{
    protected $search;

    public function __construct($search)
    {
        $this->search = $search;
    }

    public function query()
    {
        return Schedule::with(['subject', 'instructor', 'section.users', 'laboratory', 'college', 'department'])
            ->when($this->search, function ($query) {
                $query->search($this->search);
            });
    }

    public function headings(): array
    {
        return [
            'Code',            // schedule_code
            'Section',         // section name
            'Subject Code',    // subject code
            'Subject Name',    // subject name
            'Instructor',      // instructor name
            'College',         // college name
            'Department',      // department name
            'Laboratory',      // laboratory name
            'Schedule',        // formatted days and time
            'Total Students'   // count of students in the section
        ];
    }

    public function map($schedule): array
{
    $studentsCount = $schedule->section ? $schedule->section->users->count() : 0;
    $studentsCount = $studentsCount ?: '0';  // Ensure it's always "0" if no students

    // Format the schedule to show days as "Mon, Wed" and times like "(7:00 AM - 8:30 AM)"
    $scheduleTime = Carbon::parse($schedule->start_time)->format('h:i A') . ' - ' . Carbon::parse($schedule->end_time)->format('h:i A');
    $daysOfWeek = $this->getShortenedDays(json_decode($schedule->days_of_week));
    $formattedSchedule = $daysOfWeek . ' (' . $scheduleTime . ')';

    return [
        $schedule->schedule_code,                      // Code
        optional($schedule->section)->name,            // Section
        optional($schedule->subject)->code,            // Subject Code
        optional($schedule->subject)->name,            // Subject Name
        optional($schedule->instructor)->full_name,    // Instructor
        optional($schedule->college)->name,            // College
        optional($schedule->department)->name,         // Department
        optional($schedule->laboratory)->name,         // Laboratory
        $formattedSchedule,                            // Schedule (shortened days and time)
        $studentsCount,                                // Total Students
    ];
}

    // Helper function to shorten days of the week
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
                // Apply header style with the specified color and white font
                $headerStyle = [
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], // White font
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF4F81BD'], // Blue background
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ];

                // Style for the data cells
                $contentStyle = [
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ];

                // Apply header style
                $event->sheet->getStyle('A1:J1')->applyFromArray($headerStyle);

                // Apply content style
                $highestRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle('A2:J' . $highestRow)->applyFromArray($contentStyle);

                // Auto-size columns
                foreach (range('A', 'J') as $columnID) {
                    $event->sheet->getColumnDimension($columnID)->setAutoSize(true);
                }
            },
        ];
    }
}
