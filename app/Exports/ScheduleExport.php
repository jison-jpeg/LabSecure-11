<?php

namespace App\Exports;

use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class ScheduleExport implements FromQuery, WithHeadings, WithMapping, WithEvents
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
        return Schedule::with(['subject', 'instructor', 'section.users', 'laboratory', 'college', 'department'])
            ->when($this->search, function ($query) {
                $query->search($this->search);
            })
            ->when($this->user->isAdmin(), function ($query) {
                // Admin sees all schedules
            })
            ->when($this->user->isDean(), function ($query) {
                $query->where('college_id', $this->user->college_id); // Filter by Dean's college
            })
            ->when($this->user->isChairperson(), function ($query) {
                $query->where('department_id', $this->user->department_id); // Filter by Chairperson's department
            })
            ->when($this->user->isInstructor(), function ($query) {
                $query->where('instructor_id', $this->user->id); // Filter by Instructor's schedules
            })
            ->when($this->user->isStudent(), function ($query) {
                $query->where('section_id', $this->user->section_id); // Filter by Student's section
            });
    }

    public function headings(): array
    {
        return [
            'Code',
            'Section',
            'Year Level',
            'Subject',
            'Instructor',
            'Days',
            'Start Time',
            'End Time',
        ];
    }

    public function map($schedule): array
    {
        return [
            $schedule->schedule_code,
            optional($schedule->section)->name,
            optional($schedule->section)->year_level,
            optional($schedule->subject)->name,
            optional($schedule->instructor)->full_name,
            $this->getShortenedDays(json_decode($schedule->days_of_week)),
            Carbon::parse($schedule->start_time)->format('h:i A'),
            Carbon::parse($schedule->end_time)->format('h:i A'),
        ];
    }

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
                $headerStyle = [
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ];
                $event->sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
                $event->sheet->getColumnDimension('A')->setAutoSize(true);
                $event->sheet->getColumnDimension('B')->setAutoSize(true);
                $event->sheet->getColumnDimension('C')->setAutoSize(true);
                $event->sheet->getColumnDimension('D')->setAutoSize(true);
                $event->sheet->getColumnDimension('E')->setAutoSize(true);
                $event->sheet->getColumnDimension('F')->setAutoSize(true);
                $event->sheet->getColumnDimension('G')->setAutoSize(true);
                $event->sheet->getColumnDimension('H')->setAutoSize(true);
            },
        ];
    }
}
