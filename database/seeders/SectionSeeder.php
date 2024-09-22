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
            'name' => '4A',
            'year_level' => '4',
            'semester' => '1st Semester',
            'school_year' => '2024-2025',
            'college_id' => 1,
            'department_id' => 1,
        ]);

        Section::create([
            'name' => '4B',
            'year_level' => '4',
            'semester' => '1st Semester',
            'school_year' => '2024-2025',
            'college_id' => 1,
            'department_id' => 1,
        ]);
    }
}
