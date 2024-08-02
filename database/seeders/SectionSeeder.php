<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Section::create([
            'name' => 'Section A',
            'year_level' => '1st Year',
            'semester' => '1st Semester',
            'school_year' => '2021-2022',
            'college_id' => 1,
            'department_id' => 1,
        ]);

        Section::create([
            'name' => 'Section B',
            'year_level' => '1st Year',
            'semester' => '1st Semester',
            'school_year' => '2021-2022',
            'college_id' => 1,
            'department_id' => 1,
        ]);

        Section::create([
            'name' => 'Section C',
            'year_level' => '1st Year',
            'semester' => '1st Semester',
            'school_year' => '2021-2022',
            'college_id' => 1,
            'department_id' => 1,
        ]);
    }
}
