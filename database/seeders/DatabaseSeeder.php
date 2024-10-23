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
      /*  Autor::factory(1)->create();
       Editorial::factory(1)->create();*/
        Libro::factory(1)->create();
      /*  Lector::factory(1)->create();
        Resena::factory(1)->create();
        Prestamo::factory(1)->create();
        Publicacion::factory(1)->create();
        EventoLiterario::factory(1)->create();
        Inventario::factory(1)->create();
        ParticipacionEvento::factory(1)->create();*/


    }
}
