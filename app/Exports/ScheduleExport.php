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
        return Schedule::with(['subject', 'instructor', 'section.users', 'laboratory'])
            ->when($this->search, function ($query) {
                $query->search($this->search);
            });
    }

    public function headings(): array
    {
        return [
            'Subject Name', 'Instructor', 'Section', 'Days of the Week', 'Time', 'Laboratory/Room', 'Total Students'
        ];
    }

    public function map($schedule): array
    {
        $studentsCount = $schedule->section ? $schedule->section->users->count() : 0;
        $studentsCount = $studentsCount ?: '0';  // Ensure it's always "0" if no students

        // Format time to 12-hour format with AM/PM
        $scheduleTime = Carbon::parse($schedule->start_time)->format('h:i A') . ' - ' . Carbon::parse($schedule->end_time)->format('h:i A');
        $daysOfWeek = implode(', ', json_decode($schedule->days_of_week));

        return [
            optional($schedule->subject)->name,
            optional($schedule->instructor)->full_name,
            optional($schedule->section)->name,
            $daysOfWeek,
            $scheduleTime, // 12-hour format
            optional($schedule->laboratory)->name,
            $studentsCount, // Total students enrolled
        ];
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
                $event->sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

                // Apply content style
                $highestRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle('A2:G' . $highestRow)->applyFromArray($contentStyle);

                // Auto-size columns
                foreach (range('A', 'G') as $columnID) {
                    $event->sheet->getColumnDimension($columnID)->setAutoSize(true);
                }
            },
        ];
    }
}
