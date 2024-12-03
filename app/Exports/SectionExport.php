<?php

namespace App\Exports;

use App\Models\College;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class SectionExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected $rowNumber = 1; // Row numbering starts at 1 and increments

    protected $search;
    protected $college;
    protected $department;

    public function __construct($search = null, $college = null, $department = null)
    {
        $this->search = $search;
        $this->college = $college;
        $this->department = $department;
    }

    public function collection()
    {
        // Retrieve all colleges with their related departments and sections, filtered as necessary
        return College::with(['departments.sections' => function ($query) {
            $query->when($this->department, function ($query) {
                $query->where('department_id', $this->department);
            });
        }])
            ->when($this->college, function ($query) {
                $query->where('id', $this->college);
            })
            ->get();
    }

    public function headings(): array
    {
        return [
            ['#', 'COLLEGE', 'DEPARTMENT', 'SECTION', 'STUDENTS COUNT']
        ];
    }

    public function map($college): array
    {
        $rows = [];

        // Iterate through each department in the college
        foreach ($college->departments as $department) {
            // If the department has sections, map them
            if ($department->sections->isNotEmpty()) {
                foreach ($department->sections as $section) {
                    $rows[] = [
                        $this->rowNumber++, // Increment row number
                        $college->name, // College Name
                        $department->name, // Department Name
                        $section->name, // Section Name
                        $section->students->count(), // Students Count
                    ];
                }
            } else {
                // If no sections, map only the department with an empty section field
                $rows[] = [
                    $this->rowNumber++, // Increment row number
                    $college->name, // College Name
                    $department->name, // Department Name
                    '', // Empty Section Name
                    0, // Students Count
                ];
            }
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Apply styles to the headers (A1:E1)
                $headerStyle = [
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFFFF'], // White text
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF4F81BD'], // Dark blue background
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ];

                // Apply styles to the content (A2 onwards)
                $contentStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'], // Black borders
                        ],
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ];

                // Apply the header style
                $event->sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

                // Apply the content style for all rows (starting from A2)
                $highestRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle("A2:E$highestRow")->applyFromArray($contentStyle);

                // Auto-size the columns to fit the text
                foreach (range('A', 'E') as $columnID) {
                    $event->sheet->getDelegate()->getColumnDimension($columnID)->setAutoSize(true);
                }
            },
        ];
    }
}
