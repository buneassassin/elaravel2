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
        Role::create(['name' => 'Jugadorinctivo']);
        Role::create(['name' => 'Jugador']);
        Role::create(['name' => 'Administrator']);
        //Creamos un usario admin con contaseña 123456789
        User::create([
            'name' => 'Admin',
            'email' => 'K6p6M@example.com',
            'password' => bcrypt('123456789'),
            'role_id' => 3,
            'is_active' => true,
            'phone' => '123456789',
        ]);
    }
}
