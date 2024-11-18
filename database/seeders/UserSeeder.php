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
        $deanRole = Role::where('name', 'dean')->first();
        $chairpersonRole = Role::where('name', 'chairperson')->first();

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

        // Dean User
        User::create([
            'rfid_number' => 'RFID1001',
            'first_name' => 'Dean',
            'last_name' => 'User',
            'username' => 'D-1000',
            'email' => 'dean@buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $deanRole->id,
            'college_id' => 1,
        ]);

        // Chairperson User
        User::create([
            'rfid_number' => 'RFID1003',
            'first_name' => 'Chairperson',
            'last_name' => 'User',
            'username' => 'C-1000',
            'email' => 'chairperson@buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $chairpersonRole->id,
            'department_id' => 1,
            'college_id' => 1,
        ]);

        // Student User
        User::create([
            'rfid_number' => 'D7CA7675',
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
            'rfid_number' => 'B3F4F048',
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
            'rfid_number' => '64282183',
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

        User::create([
            'rfid_number' => '404DE751',
            'first_name' => 'Brigitte',
            'middle_name' => '',
            'last_name' => 'Deehay',
            'username' => '2101105657',
            'email' => '2101105657@student.buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $studentRole->id,
            'department_id' => 1,
            'college_id' => 1,
            'section_id' => 1,
        ]);

        User::create([
            'rfid_number' => 'B0C0B2AC',
            'first_name' => 'Ramon Paulo',
            'middle_name' => 'Antonio',
            'last_name' => 'Caumban',
            'username' => '2101102020',
            'email' => '2101102020@student.buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $studentRole->id,
            'department_id' => 1,
            'college_id' => 1,
            'section_id' => 1,
        ]);

        User::create([
            'rfid_number' => '92FB624B',
            'first_name' => 'Djeikuje Nickolai',
            'middle_name' => '',
            'last_name' => 'Gacus',
            'username' => '2101104946',
            'email' => '2101104946@student.buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $studentRole->id,
            'department_id' => 1,
            'college_id' => 1,
            'section_id' => 1,
        ]);

        User::create([
            'rfid_number' => '732379DE',
            'first_name' => 'Brendyl Shayne',
            'middle_name' => 'Mendoza',
            'last_name' => 'Singayao',
            'username' => '2101101088',
            'email' => '2101101088@student.buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $studentRole->id,
            'department_id' => 1,
            'college_id' => 1,
            'section_id' => 1,
        ]);

        User::create([
            'rfid_number' => '13845CD7',
            'first_name' => 'Clint Lloyd',
            'middle_name' => 'Morcilla',
            'last_name' => 'Gallardo',
            'username' => '2101105822',
            'email' => '2101105822@student.buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $studentRole->id,
            'department_id' => 1,
            'college_id' => 1,
            'section_id' => 1,
        ]);
        
        User::create([
            'rfid_number' => '167D2379',
            'first_name' => 'Kyla Honey Lette',
            'middle_name' => 'Chua-sien',
            'last_name' => 'Lin-ao',
            'username' => '2101101943',
            'email' => '2101101943@student.buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $studentRole->id,
            'department_id' => 1,
            'college_id' => 1,
            'section_id' => 1,
        ]);

        User::create([
            'rfid_number' => '902B9EAC',
            'first_name' => 'Jayson',
            'middle_name' => 'Antoniego',
            'last_name' => 'Calfoforo',
            'username' => '2101102649',
            'email' => '2101102649@student.buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $studentRole->id,
            'department_id' => 1,
            'college_id' => 1,
            'section_id' => 1,
        ]);

        User::create([
            'rfid_number' => 'C098C5AC',
            'first_name' => 'Nimrod',
            'middle_name' => 'Edadis',
            'last_name' => 'Paguta',
            'username' => '2101102736',
            'email' => '2101102736@student.buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $studentRole->id,
            'department_id' => 1,
            'college_id' => 1,
            'section_id' => 1,
        ]);

        User::create([
            'rfid_number' => '408A67AC',
            'first_name' => 'Charlotte Anne',
            'middle_name' => 'Timogan',
            'last_name' => 'Nulo',
            'username' => '2101104774',
            'email' => '2101104774@student.buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $studentRole->id,
            'department_id' => 1,
            'college_id' => 1,
            'section_id' => 1,
        ]);

        User::create([
            'rfid_number' => 'F6B65A79',
            'first_name' => 'Amiel Jay',
            'middle_name' => 'Salazar',
            'last_name' => 'Ocier',
            'username' => '2101102216',
            'email' => '2101102216@student.buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $studentRole->id,
            'department_id' => 1,
            'college_id' => 1,
            'section_id' => 1,
        ]);

        User::create([
            'rfid_number' => 'B3EA7EDE',
            'first_name' => 'Icy',
            'middle_name' => 'Aput',
            'last_name' => 'Marimon',
            'username' => '2101105139',
            'email' => '2101105139@student.buksu.edu.ph',
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
            'username' => 'F-1000',
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
            'username' => 'F-1001',
            'email' => 'gilcagande@buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $instructorRole->id,
            'department_id' => 1,
            'college_id' => 1,
        ]);

        User::create([
            'rfid_number' => 'RFID1007',
            'first_name' => 'Klevie Jun',
            'last_name' => 'Caseres',
            'username' => 'F-1002',
            'email' => 'kleviecaseres@buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $instructorRole->id,
            'department_id' => 1,
            'college_id' => 1,
        ]);

        User::create([
            'rfid_number' => 'RFID1008',
            'first_name' => 'Mark Ian',
            'last_name' => 'Mukara',
            'username' => 'F-1003',
            'email' => 'mimukara@buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $instructorRole->id,
            'department_id' => 1,
            'college_id' => 1,
        ]);

        User::create([
            'rfid_number' => 'RFID1009',
            'first_name' => 'Peter Joseph',
            'last_name' => 'Rabanes',
            'username' => 'F-1004',
            'email' => 'peterjosephrabanes@buksu.edu.ph',
            'password' => Hash::make('password'),
            'role_id' => $instructorRole->id,
            'department_id' => 1,
            'college_id' => 1,
        ]);

        // User::factory(count: 50)->create();
    }
}
