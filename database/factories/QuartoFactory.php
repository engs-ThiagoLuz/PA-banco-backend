<?php

namespace Database\Factories;

use App\Models\Departamento;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuartoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'numero'          => fake()->numerify('###'),
            'tipo'            => fake()->randomElement(['Enfermaria', 'Apartamento', 'UTI']),
            'capacidade'      => fake()->numberBetween(1, 4),
            'departamento_id' => Departamento::factory(),
        ];
    }
}
