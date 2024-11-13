<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Autor;
use App\Models\Editorial;
use App\Models\EventoLiterario;
use App\Models\Inventario;
use App\Models\ParticipacionEvento;
use App\Models\Publicacion;
use App\Models\Resena;
use App\Models\Lector;
use App\Models\Prestamo;
use App\Models\Libro;
use App\Models\Role;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        Autor::factory(5)->create();
        Editorial::factory(5)->create();
        Libro::factory(5)->create();
        Lector::factory(5)->create();
        Resena::factory(5)->create();
        Prestamo::factory(5)->create();
        Publicacion::factory(5)->create();
        EventoLiterario::factory(5)->create();
        Inventario::factory(5)->create();
        ParticipacionEvento::factory(5)->create();


    }
}
