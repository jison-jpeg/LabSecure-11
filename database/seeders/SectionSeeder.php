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
            'college_id' => 1,
            'department_id' => 1,
        ]);

        Section::create([
            'name' => 'Section B',
            'college_id' => 1,
            'department_id' => 1,
        ]);

        Section::create([
            'name' => 'Section C',
            'college_id' => 1,
            'department_id' => 1,
        ]);
    }
}
