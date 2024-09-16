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
            'rfid_number' => 'ADMIN1234',
            'first_name' => 'Admin',
            'middle_name' => '',
            'last_name' => 'User',
            'username' => 'admin',
            'email' => 'admin@buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
        ]);

        User::create([
            'rfid_number' => 'ADMIN5678',
            'first_name' => 'Exhan',
            'last_name' => 'Bandas',
            'username' => 'exhan.bandas',
            'email' => 'exhan.bandas@example.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
        ]);

        // Student User
        User::create([
            'rfid_number' => 'RFID1001',
            'first_name' => 'Jayson',
            'middle_name' => 'Licmoan',
            'last_name' => 'Tadayca',
            'username' => '2101101090',
            'email' => '2101101090@student.buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $studentRole->id,
            'department_id' => 1,
            'college_id' => 1,
            'section_id' => 1,
        ]);

        User::create([
            'rfid_number' => 'RFID1002',
            'first_name' => 'Zsaffina Pearl',
            'middle_name' => 'Cagatcagat',
            'last_name' => 'Gepana',
            'username' => '2101101048',
            'email' => '2101101048@student.buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $studentRole->id,
            'department_id' => 1,
            'college_id' => 1,
            'section_id' => 1,
        ]);

        User::create([
            'rfid_number' => 'RFID1003',
            'first_name' => 'Rey Ivan',
            'middle_name' => 'Rubiato',
            'last_name' => 'Dionaldo',
            'username' => '2101101631',
            'email' => '2101101631@student.buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $studentRole->id,
            'department_id' => 1,
            'college_id' => 1,
            'section_id' => 1,
        ]);

        User::create([
            'rfid_number' => 'RFID1004',
            'first_name' => 'Jeffrey',
            'middle_name' => 'Mati-ong',
            'last_name' => 'Sedoro',
            'username' => '2101105691',
            'email' => '2101105691@student.buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $studentRole->id,
            'department_id' => 1,
            'college_id' => 1,
            'section_id' => 1,
        ]);

        // Instructor User
        User::create([
            'rfid_number' => 'RFID1005',
            'first_name' => 'Sales',
            'last_name' => 'Aribe',
            'suffix' => 'Jr.',
            'username' => 'T-1000',
            'email' => 'sg.aribe@buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $instructorRole->id,
            'department_id' => 1,
            'college_id' => 1,
        ]);

        User::create([
            'rfid_number' => 'RFID1006',
            'first_name' => 'Gil Nicholas',
            'last_name' => 'Cagande',
            'username' => 'T-1001',
            'email' => 'gilcagande@buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $instructorRole->id,
            'department_id' => 1,
            'college_id' => 1,
        ]);

        User::create([
            'rfid_number' => 'RFID1006',
            'first_name' => 'Klevie Jun',
            'last_name' => 'Caseres',
            'username' => 'T-1002',
            'email' => 'kleviecaseres@buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $instructorRole->id,
            'department_id' => 1,
            'college_id' => 1,
        ]);

        User::create([
            'rfid_number' => 'RFID1006',
            'first_name' => 'Mark Ian',
            'last_name' => 'Mukara',
            'username' => 'T-1003',
            'email' => 'mimukara@buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $instructorRole->id,
            'department_id' => 1,
            'college_id' => 1,
        ]);

        User::create([
            'rfid_number' => 'RFID1006',
            'first_name' => 'Peter Joseph',
            'last_name' => 'Rabanes',
            'username' => 'T-1004',
            'email' => 'peterjosephrabanes@buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $instructorRole->id,
            'department_id' => 1,
            'college_id' => 1,
        ]);

        // User::factory(count: 30)->create();
    }
}
