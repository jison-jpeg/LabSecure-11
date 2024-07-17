<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
            'role_id' => Role::where('name', 'admin')->first()->id,
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
            'role_id' => Role::where('name', 'student')->first()->id,
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
            'role_id' => Role::where('name', 'instructor')->first()->id,
        ]);
    }
}
