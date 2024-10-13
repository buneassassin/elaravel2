<?php

namespace Database\Factories;


use App\Models\Libreria;
use Illuminate\Database\Eloquent\Factories\Factory;

class LibreriaFactory extends Factory
{
    protected $model = Libreria::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->company,
            'ubicacion' => $this->faker->address,
        ];
    }
}
