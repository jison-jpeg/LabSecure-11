<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class StudentExport implements FromQuery, WithHeadings, WithMapping, WithEvents
{
    protected $search;
    protected $college;
    protected $department;
    protected $rowNumber = 0; // Initialize a row counter

    public function __construct($search, $college, $department)
    {
        $this->search = $search;
        $this->college = $college;
        $this->department = $department;
    }

    public function query()
    {
        return User::query()
            ->whereHas('role', function ($query) {
                $query->where('name', 'Student'); // Assuming "Student" is the role name for students
            })
            ->when($this->search, function ($query) {
                $query->where('username', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->when($this->college, function ($query) {
                $query->where('college_id', $this->college);
            })
            ->when($this->department, function ($query) {
                $query->where('department_id', $this->department);
            })
            ->select([
                'username',
                'email',
                'first_name',
                'middle_name',
                'last_name',
                'suffix',
                'college_id',
                'department_id',
                'section_id', // For section name
            ]);
    }

    public function headings(): array
    {
        return [
            '#',
            'Username',
            'Email',
            'Section',
            'First Name',
            'Middle Name',
            'Last Name',
            'Suffix',
            'College',
            'Department',
        ];
    }

    public function map($user): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $user->username,
            $user->email,
            optional($user->section)->name, // Section Name
            $user->first_name,
            $user->middle_name,
            $user->last_name,
            $user->suffix,
            optional($user->college)->name,
            optional($user->department)->name,
        ];
    }

    // Add the WithEvents interface to apply styles
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Styling the header row
                $headerStyle = [
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFFFF'], // White font color
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF4F81BD'], // Dark blue background
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, // Left alignment
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'], // Black borders
                        ],
                    ],
                ];

                // Apply the header style (A1:J1) since there are 10 columns now
                $event->sheet->getStyle('A1:J1')->applyFromArray($headerStyle);

                // Apply borders and left alignment to the content
                $highestRow = $event->sheet->getHighestRow(); // e.g., row 50
                $highestColumn = $event->sheet->getHighestColumn(); // e.g., column J
                $contentStyle = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, // Left alignment
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'], // Black borders for all content
                        ],
                    ],
                ];

                // Apply the content style from A2 to the last row/column
                $event->sheet->getStyle('A2:' . $highestColumn . $highestRow)->applyFromArray($contentStyle);

                // Auto-size the columns to fit the content
                foreach (range('A', 'J') as $columnID) {
                    $event->sheet->getDelegate()->getColumnDimension($columnID)->setAutoSize(true);
                }
            },
        ];
    }
}
