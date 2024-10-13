<?php

namespace Database\Factories;

use App\Models\Prestamo;
use App\Models\Libro;
use App\Models\Lector;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrestamoFactory extends Factory
{
    protected $model = Prestamo::class;

    public function definition()
    {
        return [
            'libro_id' => Libro::factory(),
            'lector_id' => Lector::factory(),
            'fecha_prestamo' => $this->faker->date,
            'fecha_devolucion' => $this->faker->date,
        ];
    }
}
