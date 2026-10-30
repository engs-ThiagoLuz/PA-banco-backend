<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EspecialidadeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => fake()->unique()->bothify('Especialidade ####'),
        ];
    }
}
