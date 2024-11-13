<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Role;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Role::class;

    public function definition()
    {
        // Definimos los roles predeterminados
        $roles = ['Guest', 'User', 'Administrator'];

        return [
            'name' => $this->faker->unique()->randomElement($roles),
        ];
    }
}
