<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Subject::create([
            'name' => 'Mathematics',
            'code' => 'MATH 101',
            'description' => 'This is a mathematics subject',
            'college_id' => 1,
            'department_id' => 1,
        ]);

        Subject::create([
            'name' => 'Science',
            'code' => 'SCI 101',
            'description' => 'This is a science subject',
            'college_id' => 1,
            'department_id' => 1,
        ]);

        Subject::create([
            'name' => 'English',
            'code' => 'ENG 101',
            'description' => 'This is an english subject',
            'college_id' => 1,
            'department_id' => 1,
        ]);
    }
}
