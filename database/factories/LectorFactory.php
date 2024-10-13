<?php

namespace Database\Factories;

use App\Models\Lector;
use Illuminate\Database\Eloquent\Factories\Factory;

class LectorFactory extends Factory
{
    protected $model = Lector::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
        ];
    }
}
