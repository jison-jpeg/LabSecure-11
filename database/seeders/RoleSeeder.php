<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'instructor']);
        Role::create(attributes: ['name' => 'student']);
        Role::create(attributes: ['name' => 'dean']);
        Role::create(attributes: ['name' => 'chairperson']);

    }
}
