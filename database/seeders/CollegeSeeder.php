<?php

namespace Database\Seeders;

use App\Models\College;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CollegeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        College::create(['name' => 'College of Technologies', 'department_id' => 1, 'section' => 'A']);
        College::create(['name' => 'College of Technologies', 'department_id' => 1, 'section' => 'B']);
        College::create(['name' => 'College of Technologies', 'department_id' => 1, 'section' => 'C']);
    }
}
