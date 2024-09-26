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
            'location' => 'Finance Building',
            'type' => 'Computer Laboratory',
            'status' => 'Available',
        ]);

        Laboratory::create([
            'name' => '2',
            'location' => 'Finance Building',
            'type' => 'Computer Laboratory',
            'status' => 'Available',
        ]);

        Laboratory::create([
            'name' => '3',
            'location' => 'Finance Building',
            'type' => 'Computer Laboratory',
            'status' => 'Available',
        ]);

        Laboratory::create([
            'name' => '4',
            'location' => 'Building 1',
            'type' => 'Computer Laboratory',
            'status' => 'Locked',
        ]);

        Laboratory::create([
            'name' => '5',
            'location' => 'Building 1',
            'type' => 'Computer Laboratory',
            'status' => 'Available',
        ]);

        Laboratory::create([
            'name' => '6',
            'location' => 'Building 1',
            'type' => 'Computer Laboratory',
            'status' => 'Available',
        ]);

        Laboratory::create([
            'name' => '7',
            'location' => 'Building 1',
            'type' => 'Multimedia Laboratory',
            'status' => 'Available',
        ]);
    }
}
