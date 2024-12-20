<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Run the seeders class
        $this->call([
            CollegeSeeder::class,
            DepartmentSeeder::class,
            LaboratorySeeder::class,
            RoleSeeder::class,
            SubjectSeeder::class,
            SectionSeeder::class,
            UserSeeder::class,
            ScheduleSeeder::class,
            AttendanceSeeder::class,
        ]);
    }
}
