<?php

namespace Database\Factories;

use App\Models\Especialidade;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicoFactory extends Factory
{
    public function definition(): array
    {
        $faker = fake('pt_BR');

        return [
            'nome'             => $faker->name(),
            'crm'              => $faker->unique()->numerify('######') . '-' . $faker->randomElement(['PR', 'SP', 'SC', 'RS']),
            'especialidade_id' => Especialidade::factory(),
            'telefone'         => $faker->cellphone(),
            'email'            => $faker->unique()->safeEmail(),
            'data_contratacao' => $faker->dateTimeBetween('-10 years', 'now')->format('Y-m-d'),
        ];
    }
}
