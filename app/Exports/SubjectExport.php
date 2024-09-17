<?php

namespace App\Exports;

use App\Models\Subject;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class SubjectExport implements FromQuery, WithHeadings, WithMapping, WithEvents
{
    protected $search;
    protected $college;
    protected $department;

    public function __construct($search, $college, $department)
    {
        $this->search = $search;
        $this->college = $college;
        $this->department = $department;
    }

    public function query()
    {
        return Subject::with(['schedules.instructor', 'schedules.section.users', 'schedules.laboratory'])
            ->when($this->search, function ($query) {
                $query->search($this->search);
            })
            ->when($this->college, function ($query) {
                $query->where('college_id', $this->college);
            })
            ->when($this->department, function ($query) {
                $query->where('department_id', $this->department);
            });
    }

    public function headings(): array
    {
        return [
            'Subject Code',
            'Subject Name',
            'Description',
            'Section',
            'Instructor',
            'Schedule',
            'Laboratory',
            'Total Students'
        ];
    }

    public function map($subject): array
    {
        $rows = [];
        foreach ($subject->schedules as $schedule) {
            $studentsCount = $schedule->section ? $schedule->section->users->count() : 0; // Fetch total students from section
            $studentsCount = $studentsCount ?: '0'; // Ensure it's always a string '0' if empty

            // Format time to 12-hour format with AM/PM
            $scheduleTime = Carbon::parse($schedule->start_time)->format('h:i A') . ' - ' . Carbon::parse($schedule->end_time)->format('h:i A');
            $daysOfWeek = implode(', ', json_decode($schedule->days_of_week));

            $rows[] = [
                $subject->code, // Subject Code first
                $subject->name, // Then Subject Name
                $subject->description,
                optional($schedule->section)->name,
                optional($schedule->instructor)->full_name,
                $daysOfWeek . ' (' . $scheduleTime . ')', // 12-hour format for schedule time
                optional($schedule->laboratory)->name,
                $studentsCount,  // Ensures "0" is displayed when no students
            ];
        }

        return $rows;
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
                $event->sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

                // Apply content style
                $highestRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle('A2:H' . $highestRow)->applyFromArray($contentStyle);

                // Auto-size columns
                foreach (range('A', 'H') as $columnID) {
                    $event->sheet->getColumnDimension($columnID)->setAutoSize(true);
                }
            },
        ];
    }
}
