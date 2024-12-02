<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class UsersExport implements FromQuery, WithHeadings, WithMapping, WithEvents
{
    protected $role;
    protected $status;
    protected $rowNumber = 0; // Initialize a row counter

    public function __construct($role, $status)
    {
        $this->role = $role;
        $this->status = $status;
    }

    public function query()
    {
        return User::query()
            ->when($this->role, function ($query) {
                $query->whereHas('role', function ($q) {
                    $q->where('name', $this->role);
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->select([
                'username',
                'first_name',
                'last_name',
                'middle_name',
                'suffix',
                'email',
                'role_id',
                'college_id',
                'department_id',
                'status',
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
            'Role',
            'College',
            'Department',
            'Status',
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
            optional($user->role)->name,
            optional($user->college)->name,
            optional($user->department)->name,
            ucfirst($user->status),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $headerStyle = [
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF4F81BD'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ];

                $event->sheet->getStyle('A1:K1')->applyFromArray($headerStyle);

                $contentStyle = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ];

                $highestRow = $event->sheet->getHighestRow();
                $highestColumn = $event->sheet->getHighestColumn();
                $event->sheet->getStyle("A2:{$highestColumn}{$highestRow}")->applyFromArray($contentStyle);

                foreach (range('A', 'K') as $columnID) {
                    $event->sheet->getDelegate()->getColumnDimension($columnID)->setAutoSize(true);
                }
            },
        ];
    }
}
