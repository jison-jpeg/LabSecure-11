<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $studentRole = Role::where('name', 'student')->first();
        $instructorRole = Role::where('name', 'instructor')->first();

        // Admin User
        User::create([
            'rfid_number' => '1234567890',
            'first_name' => 'Admin',
            'middle_name' => '',
            'last_name' => 'User',
            'suffix' => 'Jr.',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
        ]);

        // Student User
        User::create([
            'rfid_number' => '0987654321',
            'first_name' => 'Student',
            'middle_name' => '',
            'last_name' => 'User',
            'suffix' => 'Sr.',
            'username' => 'student',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'role_id' => $studentRole->id,
            'department_id' => 1,
            'college_id' => 1,
        ]);

        // Instructor User
        User::create([
            'rfid_number' => '1357924680',
            'first_name' => 'Instructor',
            'middle_name' => '',
            'last_name' => 'User',
            'suffix' => 'Sr.',
            'username' => 'instructor',
            'email' => 'instructor@example.com',
            'password' => Hash::make('password'),
            'role_id' => $instructorRole->id,
            'department_id' => 1,
            'college_id' => 1,
        ]);
    }
}
