<?php

namespace App\Exports;

use App\Models\College;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class CollegeExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected $rowNumber = 1; // Row numbering starts at 1 and will increment across different colleges

    public function collection()
    {
        // Get all colleges with their related departments
        return College::with('departments')->get();
    }

    public function headings(): array
    {
        return [
            ['#', 'COLLEGES', 'DEPARTMENTS']
        ];
    }

    public function map($college): array
    {
        $rows = [];

        // Add the first row with the college name and the first department
        if ($college->departments->isNotEmpty()) {
            // First row: College name and the first department, with row number incrementing
            $rows[] = [
                $this->rowNumber++, // Increment row number for each college
                $college->name, // College Name
                $college->departments->first()->name, // First Department
            ];

            // Add remaining departments with blank college name and blank row number
            foreach ($college->departments->skip(1) as $department) {
                $rows[] = [
                    '', // Blank Row number
                    '', // Blank College Name for subsequent rows
                    $department->name, // Department Name
                ];
            }
        } else {
            // If no departments, just list the college with an empty department field
            $rows[] = [
                $this->rowNumber++, // Increment row number
                $college->name, // College Name
                '', // No department
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Apply styles to the headers (A1:C1)
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
                $event->sheet->getStyle('A1:C1')->applyFromArray($headerStyle);

                // Apply the content style for all rows (starting from A2)
                $highestRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle("A2:C$highestRow")->applyFromArray($contentStyle);

                // Auto-size the columns to fit the text
                foreach (range('A', 'C') as $columnID) {
                    $event->sheet->getDelegate()->getColumnDimension($columnID)->setAutoSize(true);
                }
            },
        ];
    }
}
