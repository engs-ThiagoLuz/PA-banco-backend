<?php

namespace Database\Factories;

use App\Models\Convenio;
use Illuminate\Database\Eloquent\Factories\Factory;

class PacienteFactory extends Factory
{
    public function definition(): array
    {
        $faker = fake('pt_BR');

        return [
            'nome'            => $faker->name(),
            'cpf'             => $faker->unique()->cpf(),
            'data_nascimento' => $faker->dateTimeBetween('-90 years', '-1 year')->format('Y-m-d'),
            'sexo'            => $faker->randomElement(['M', 'F', 'O']),
            'telefone'        => $faker->cellphone(),
            'email'           => $faker->safeEmail(),
            'endereco'        => $faker->streetAddress(),
            'convenio_id'     => $faker->boolean(60) ? Convenio::factory() : null,
        ];
    }
}
