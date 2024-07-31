<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        // Fetch random role ids
        $roles = Role::pluck('id')->toArray();
        // Fetch the student role id
        $studentRoleId = Role::where('name', 'student')->pluck('id')->first();
        // Fetch random section ids
        $sections = Section::pluck('id')->toArray();

        $roleId = $this->faker->randomElement($roles);

        return [
            'rfid_number' => $this->faker->unique()->numerify('##########'),
            'first_name' => $this->faker->firstName,
            'middle_name' => $this->faker->lastName,
            'last_name' => $this->faker->lastName,
            'suffix' => $this->faker->suffix,
            'username' => $this->faker->unique()->userName,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make('password'),
            'role_id' => $roleId,
            'department_id' => 1, // or use a random department id
            'college_id' => 1, // or use a random college id
            'section_id' => $roleId == $studentRoleId ? $this->faker->randomElement($sections) : null,
        ];
    }
}
