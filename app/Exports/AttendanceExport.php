<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceExport implements FromQuery, WithHeadings, WithMapping, WithEvents
{
    protected $selectedMonth;
    protected $selectedSubject;
    protected $status;

    protected $userId;
    protected $scheduleId;

    public function __construct($selectedMonth, $selectedSubject, $status, $userId = null, $scheduleId = null)
    {
        $this->selectedMonth = $selectedMonth;
        $this->selectedSubject = $selectedSubject;
        $this->status = $status;
        $this->userId = $userId;
        $this->scheduleId = $scheduleId; // Add schedule ID to the constructor
    }

    public function query()
    {
        $query = Attendance::query();

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        if ($this->scheduleId) {
            $query->where('schedule_id', $this->scheduleId); // Filter by schedule ID
        }

        if ($this->selectedMonth) {
            $query->whereDate('date', 'like', $this->selectedMonth . '%');
        }

        if ($this->selectedSubject) {
            $query->whereHas('schedule.subject', function ($q) {
                $q->where('id', $this->selectedSubject);
            });
        }

        if ($this->status) {
            $query->where('status', strtolower($this->status));
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Time In',
            'Time Out',
            'Status',
            'Percentage',
            'Remarks',
        ];
    }

    public function map($attendance): array
    {
        return [
            Carbon::parse($attendance->date)->format('D - m/d/Y'), // Date (e.g., MON - 07/01/2024)
            $attendance->formattedTimeIn,                        // Time In from accessor
            $attendance->formattedTimeOut,                       // Time Out from accessor
            ucfirst($attendance->status),                        // Status (Capitalized)
            $attendance->percentage ?? 'N/A',                     // Percentage (Assuming accessor or null)
            $attendance->remarks ?? '',                           // Remarks
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Style the header row with blue background
                $headerStyle = [
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFFFF'], // White text color
                    ],
                    'alignment' => ['horizontal' => 'left'], // Left-align the header text
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF4F81BD'], // Dark blue background
                    ]
                ];

                // Apply the header style to columns A1:F1
                $event->sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

                // Apply left alignment and borders to all data cells
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

                // Apply the content style to the entire data range (A2:F{last row})
                $highestRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle("A2:F{$highestRow}")->applyFromArray($contentStyle);

                // Auto-size the columns A to F
                foreach (range('A', 'F') as $columnID) {
                    $event->sheet->getColumnDimension($columnID)->setAutoSize(true);
                }
            },
        ];
    }
}
