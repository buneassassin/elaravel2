<?php

namespace Database\Factories;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Publicacion>
 */use App\Models\Publicacion;
use App\Models\Libro;
use App\Models\Editorial;
use Illuminate\Database\Eloquent\Factories\Factory;

class PublicacionFactory extends Factory
{
    protected $model = Publicacion::class;

    public function definition()
    {
        return [
            'libro_id' => Libro::factory(),
            'editorial_id' => Editorial::factory(),
            'fecha_publicacion' => $this->faker->date,
        ];
    }
}
