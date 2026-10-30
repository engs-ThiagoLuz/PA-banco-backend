<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ConvenioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome'     => fake('pt_BR')->unique()->company(),
            'telefone' => fake('pt_BR')->landline(),
        ];
    }
}
