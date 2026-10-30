<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DepartamentoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome'        => fake()->unique()->bothify('Departamento ###'),
            'localizacao' => 'Bloco ' . fake()->randomElement(['A', 'B', 'C', 'D'])
                . ', ' . fake()->numberBetween(1, 5) . 'º andar',
        ];
    }
}
