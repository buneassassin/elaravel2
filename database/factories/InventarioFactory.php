<?php

namespace Database\Factories;

use App\Models\Inventario;
use App\Models\Libreria;
use App\Models\Libro;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventarioFactory extends Factory
{
    protected $model = Inventario::class;

    public function definition()
    {
        return [
            'libreria_id' => Libreria::factory(),
            'libro_id' => Libro::factory(),
            'cantidad' => $this->faker->numberBetween(1, 100),
        ];
    }
}