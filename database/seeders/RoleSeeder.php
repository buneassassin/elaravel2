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
    }
}
