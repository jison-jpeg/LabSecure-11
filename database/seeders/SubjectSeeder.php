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
            'name' => 'Systems Administration and Maintenance',
            'code' => 'IT 141A',
            'description' => 'This is a Systems Administration and Maintenance subject',
            'college_id' => 1,
            'department_id' => 1,
        ]);

        Subject::create([
            'name' => 'Business Analytics',
            'code' => 'IT 142',
            'description' => 'This is a Business Analytics subject',
            'college_id' => 1,
            'department_id' => 1,
        ]);

        Subject::create([
            'name' => 'Quality Consciousness, Habits and Processes',
            'code' => 'IT 143',
            'description' => 'This is a Quality Consciousness, Habits and Processes subject',
            'college_id' => 1,
            'department_id' => 1,
        ]);

        Subject::create([
            'name' => 'Capstone Project and Research 3',
            'code' => 'IT 144',
            'description' => 'This is a Capstone Project and Research 3 subject',
            'college_id' => 1,
            'department_id' => 1,
        ]);

        Subject::create([
            'name' => 'Social and Professional Issues in IT',
            'code' => 'IT 145',
            'description' => 'This is a Social and Professional Issues in IT subject',
            'college_id' => 1,
            'department_id' => 1,
        ]);

        Subject::create([
            'name' => 'Digital Marketing',
            'code' => 'IT 146',
            'description' => 'This is a Digital Marketing subject',
            'college_id' => 1,
            'department_id' => 1,
        ]);
    }
}
