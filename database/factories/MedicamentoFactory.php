<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MedicamentoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome'       => fake()->randomElement([
                'Dipirona', 'Paracetamol', 'Ibuprofeno', 'Amoxicilina', 'Omeprazol',
                'Losartana', 'Metformina', 'Dexametasona', 'Azitromicina', 'Loratadina',
            ]),
            'fabricante' => fake()->randomElement(['EMS', 'Eurofarma', 'Medley', 'Neo Química', 'Sanofi', 'Aché']),
            'tipo'       => fake()->randomElement(['Comprimido', 'Cápsula', 'Xarope', 'Injetável', 'Pomada']),
        ];
    }
}
