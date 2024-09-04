<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return User::with(['role', 'college', 'department', 'sections'])
            ->get()
            ->map(function ($user) {
                return [
                    'username' => $user->username,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'middle_name' => $user->middle_name ?? '',
                    'suffix' => $user->suffix ?? '',
                    'email' => $user->email,
                    'role_name' => $user->role->name,
                    'college_name' => $user->college->name ?? '',
                    'department_name' => $user->department->name ?? '',
                    'section_name' => $user->sections->pluck('name')->implode(', ') ?? '',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Username',
            'First Name',
            'Last Name',
            'Middle Name',
            'Suffix',
            'Email',
            'Role',
            'College',
            'Department',
            'Section(s)',
        ];
    }
}
