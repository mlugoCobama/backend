<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class DatosAcumuladosFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'anio' => fake()->year(),
            'mes' => fake()->month(),
            'valor' => fake()->randomFloat(2, 0, 1000000),
        ];
    }
}
