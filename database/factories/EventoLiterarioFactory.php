<?php

namespace Database\Factories;


use App\Models\EventoLiterario;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventoLiterarioFactory extends Factory
{
    protected $model = EventoLiterario::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->sentence,
            'fecha' => $this->faker->date,
            'ubicacion' => $this->faker->address,
        ];
    }
}
