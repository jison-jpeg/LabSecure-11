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
    protected $selectedSection;
    protected $selectedCollege;
    protected $selectedDepartment;
    protected $status;
    protected $search;
    protected $sortBy;
    protected $sortDir;

    public function __construct($selectedMonth, $selectedSubject, $selectedSection, $selectedCollege, $selectedDepartment, $status, $search, $sortBy, $sortDir)
    {
        $this->selectedMonth = $selectedMonth;
        $this->selectedSubject = $selectedSubject;
        $this->selectedSection = $selectedSection;
        $this->selectedCollege = $selectedCollege;
        $this->selectedDepartment = $selectedDepartment;
        $this->status = $status;
        $this->search = $search;
        $this->sortBy = $sortBy;
        $this->sortDir = $sortDir;
    }

    public function query()
    {
        $user = Auth::user();

        $query = Attendance::with(['user', 'schedule.subject', 'schedule.section', 'schedule.laboratory', 'sessions'])
            ->orderBy($this->sortBy, 'ASC');

        // Apply Role-Based Access Control
        if ($user->isAdmin()) {
            // Admin: All attendance records with optional College and Department filters
            if ($this->selectedCollege) {
                $query->whereHas('user', function ($q) {
                    $q->where('college_id', $this->selectedCollege);
                });
            }

            if ($this->selectedDepartment) {
                $query->whereHas('user', function ($q) {
                    $q->where('department_id', $this->selectedDepartment);
                });
            }
        } elseif ($user->isDean()) {
            // Dean: Attendances within their College
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('college_id', $user->college_id);
            });
        } elseif ($user->isChairperson()) {
            // Chairperson: Attendances within their Department
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        } elseif ($user->isInstructor()) {
            // Instructor: Only their own attendance records
            $query->where('user_id', $user->id);
        } else {
            // Other users: Only their own attendance records
            $query->where('user_id', $user->id);
        }

        // Apply Search Filters
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->whereHas('user', function ($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('middle_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('suffix', 'like', '%' . $this->search . '%')
                      ->orWhere('username', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('schedule.subject', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('schedule', function ($q) {
                    $q->where('schedule_code', 'like', '%' . $this->search . '%');
                })
                ->orWhere('date', 'like', '%' . $this->search . '%')
                ->orWhere('status', 'like', '%' . $this->search . '%');
            });
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

        // Apply Section Filter
        if (!empty($this->selectedSection)) {
            $query->whereHas('schedule.section', function ($q) {
                $q->where('id', $this->selectedSection);
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
            Carbon::parse($attendance->date)->format('m/d/Y'), // Date
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
