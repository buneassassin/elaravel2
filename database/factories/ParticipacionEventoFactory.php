<?php

namespace Database\Factories;

use App\Models\ParticipacionEvento;
use App\Models\EventoLiterario;
use App\Models\Autor;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParticipacionEventoFactory extends Factory
{
    protected $model = ParticipacionEvento::class;

    public function definition()
    {
        return [
            'evento_id' => EventoLiterario::factory(),
            'autor_id' => Autor::factory(),
        ];
    }
}