<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromQuery, WithHeadings, WithMapping
{
    protected $search;
    protected $role;
    protected $rowNumber = 0; // Initialize a row counter

    public function __construct($search, $role)
    {
        $this->search = $search;
        $this->role = $role;
    }

    public function query()
    {
        return User::query()
            ->when($this->search, function ($query) {
                $query->where('username', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->when($this->role, function ($query) {
                $query->where('role_id', $this->role);
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
                'section_id',
            ]); // Select only the required columns
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
            'Role ID', 
            'College ID', 
            'Department ID', 
            'Section ID',
        ]; // Updated headings to match your requirements
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
            optional($user->section)->name, 
        ];
    }
}
