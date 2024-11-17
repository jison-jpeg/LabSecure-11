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

    public function __construct($selectedMonth, $selectedSubject, $status)
    {
        $this->selectedMonth = $selectedMonth;
        $this->selectedSubject = $selectedSubject;
        $this->status = $status;
    }

    public function query()
    {
        $user = Auth::user();

        $query = Attendance::with(['user', 'schedule.subject'])
            ->orderBy('date', 'ASC'); // Default ordering by date

        // Apply Role-Based Access Control
        if ($user->isAdmin()) {
            // Admin: All attendance records with optional Subject and Status filters
            // No additional filtering needed unless specified
        } elseif ($user->isInstructor()) {
            // Instructor: Only their own attendance records
            $query->where('user_id', $user->id);
        } else {
            // Other users: Only their own attendance records
            $query->where('user_id', $user->id);
        }

        // Apply Status Filter
        if (!empty($this->status)) {
            $query->where('status', strtolower($this->status));
        }

        // Apply Subject Filter
        if (!empty($this->selectedSubject)) {
            $query->whereHas('schedule.subject', function ($q) {
                $q->where('id', $this->selectedSubject);
            });
        }

        // Apply Month Filter
        if ($this->selectedMonth) {
            try {
                $parsedMonth = Carbon::parse($this->selectedMonth);
                $query->whereMonth('date', $parsedMonth->month)
                      ->whereYear('date', $parsedMonth->year);
            } catch (\Exception $e) {
                // Handle invalid date format if necessary
            }
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
