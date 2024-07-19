<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Department::create([
            'name' => 'Information Technology',
            'college_id' => 1,
        ]);
        Department::create([
            'name' => 'Entertainment MC',
            'college_id' => 2,
        ]);
        Department::create([
            'name' => 'Food Technology',
            'college_id' => 1,
        ]);
        Department::create([
            'name' => 'Automotive Technology',
            'college_id' => 2,
        ]);
        Department::create([
            'name' => 'Electronics Technology',
            'college_id' => 2,
        ]);
        Department::create([
            'name' => 'Accountancy',
            'college_id' => 3,
        ]);
        Department::create([
            'name' => 'Business Administration',
            'college_id' => 3,
        ]);
    }
}