<?php

namespace Database\Seeders;

use App\Models\Laboratory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LaboratorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Laboratory::create([
            'name' => '1',
            'location' => 'Building 1',
            'type' => 'Computer Laboratory',
            'status' => 'Available',
        ]);

        Laboratory::create([
            'name' => '2',
            'location' => 'Building 1',
            'type' => 'Computer Laboratory',
            'status' => 'Available',
        ]);

        Laboratory::create([
            'name' => '3',
            'location' => 'Building 1',
            'type' => 'EMC Laboratory',
            'status' => 'Available',
        ]);

        Laboratory::create([
            'name' => '4',
            'location' => 'Building 1',
            'type' => 'EMC Laboratory',
            'status' => 'Locked',
        ]);

        Laboratory::create([
            'name' => '5',
            'location' => 'Building 1',
            'type' => 'EMC Laboratory',
            'status' => 'Available',
        ]);
    }
}
