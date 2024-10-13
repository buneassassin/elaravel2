<?php

namespace Database\Factories;

use App\Models\Resena;
use App\Models\Libro;
use App\Models\Lector;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResenaFactory extends Factory
{
    protected $model = Resena::class;

    public function definition()
    {
        return [
            'libro_id' => Libro::factory(),
            'lector_id' => Lector::factory(),
            'calificacion' => $this->faker->numberBetween(1, 5),
            'comentario' => $this->faker->paragraph,
        ];
    }
}
