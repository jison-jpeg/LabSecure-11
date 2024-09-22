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
        College::create([
            'name' => 'College of Technologies',
            'description' => 'The College of Technologies is the premier institution for technology education in the country.',
        ]);
        // College::create([
        //     'name' => 'College of Arts and Sciences',
        // ]);
        // College::create([
        //     'name' => 'College of Business Administration',
        // ]);
    }
}
