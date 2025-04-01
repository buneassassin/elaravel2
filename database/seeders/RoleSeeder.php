<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Crea los roles predeterminados
        Role::create(['name' => 'Guest']);
        Role::create(['name' => 'User']);
        Role::create(['name' => 'Administrator']);

        User::create([
            'name' => 'Admin',
            'email' => 'K6p6M@example.com',
            'password' => bcrypt('123456789'),
            'profile_picture' => 'https://ui-avatars.com/api/?name=Admin&color=7F9CF5&background=EBF4FF',
            'role_id' => 3,
            'is_active' => true,
        ]);
    }
}
