<?php

namespace Database\Factories;

use App\Models\Libro;
use App\Models\Autor;
use Illuminate\Database\Eloquent\Factories\Factory;

class LibroFactory extends Factory
{
    protected $model = Libro::class;

    public function definition()
    {
        return [
            'titulo' => $this->faker->sentence,
            'genero' => $this->faker->word,
            'autor_id' => Autor::factory(),
        ];
    }
}
