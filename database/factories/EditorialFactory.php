<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Editorial>
 */
use App\Models\Editorial;

class EditorialFactory extends Factory
{
    protected $model = Editorial::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->company,
            'pais' => $this->faker->country,
        ];
    }
}