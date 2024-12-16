<?php

namespace App\Exports;

use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class SubjectExport implements FromQuery, WithHeadings, WithMapping, WithEvents
{
    protected $search;
    protected $user;

    public function __construct($search, $user)
    {
        $this->search = $search;
        $this->user = $user;
    }

    public function query()
    {
        return Subject::with(['schedules.instructor', 'schedules.section.users', 'schedules.laboratory'])
            ->when($this->search, function ($query) {
                $query->search($this->search);
            })
            ->when($this->user->isStudent(), function ($query) {
                $query->whereHas('schedules.section', function ($q) {
                    $q->where('id', $this->user->section_id);
                });
            })
            ->when($this->user->isInstructor(), function ($query) {
                $query->whereHas('schedules', function ($q) {
                    $q->where('instructor_id', $this->user->id);
                });
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
            'Total Students',
        ];
    }

    public function map($subject): array
    {
        $rows = [];
        foreach ($subject->schedules as $schedule) {
            $studentsCount = $schedule->section ? $schedule->section->users->count() : 0;
            $scheduleTime = Carbon::parse($schedule->start_time)->format('h:i A') . ' - ' . Carbon::parse($schedule->end_time)->format('h:i A');
            $daysOfWeek = implode(', ', json_decode($schedule->days_of_week));

            $rows[] = [
                $subject->code,
                $subject->name,
                $subject->description,
                optional($schedule->section)->name,
                optional($schedule->instructor)->full_name,
                $daysOfWeek . ' (' . $scheduleTime . ')',
                optional($schedule->laboratory)->name,
                $studentsCount,
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $headerStyle = [
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF4F81BD'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ];

                $contentStyle = [
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ];

                $event->sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

                $highestRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle('A2:H' . $highestRow)->applyFromArray($contentStyle);

                foreach (range('A', 'H') as $columnID) {
                    $event->sheet->getColumnDimension($columnID)->setAutoSize(true);
                }
            },
        ];
    }
}
