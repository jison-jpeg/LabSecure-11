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
                $query->where('name', 'Instructor'); // Assuming "Instructor" is the role name for faculty
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
                'first_name',
                'last_name',
                'middle_name',
                'suffix',
                'email',
                'college_id',
                'department_id',
            ]);
    }

    public function headings(): array
    {
        return [
            '#',
            'Username',
            'First Name',
            'Last Name',
            'Middle Name',
            'Suffix',
            'Email',
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
            $user->first_name,
            $user->last_name,
            $user->middle_name,
            $user->suffix,
            $user->email,
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

                // Apply the header style (A1:I1) since there are 9 columns now
                $event->sheet->getStyle('A1:I1')->applyFromArray($headerStyle);

                // Apply borders and left alignment to the content
                $highestRow = $event->sheet->getHighestRow(); // e.g., row 50
                $highestColumn = $event->sheet->getHighestColumn(); // e.g., column I
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
                foreach (range('A', 'I') as $columnID) {
                    $event->sheet->getDelegate()->getColumnDimension($columnID)->setAutoSize(true);
                }
            },
        ];
    }
}
