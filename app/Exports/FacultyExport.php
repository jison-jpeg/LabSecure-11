<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class FacultyExport implements FromQuery, WithHeadings, WithMapping, WithEvents
{
    protected $college;
    protected $department;

    public function __construct($college, $department)
    {
        $this->college = $college;
        $this->department = $department;
    }

    public function query()
{
    return User::query()
        ->whereHas('role', function ($q) {
            $q->where('name', 'instructor'); // Use role name "instructor"
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
            '#',
            'Username',
            'First Name',
            'Last Name',
            'Email',
            'College',
            'Department',
        ];
    }

    public function map($faculty): array
    {
        return [
            $faculty->id,
            $faculty->username,
            $faculty->first_name,
            $faculty->last_name,
            $faculty->email,
            optional($faculty->college)->name,
            optional($faculty->department)->name,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:G1')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF4F81BD'],
                    ],
                ]);

                foreach (range('A', 'G') as $columnID) {
                    $event->sheet->getColumnDimension($columnID)->setAutoSize(true);
                }
            },
        ];
    }
}
