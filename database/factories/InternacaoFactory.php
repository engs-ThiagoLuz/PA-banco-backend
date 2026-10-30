<?php

namespace Database\Factories;

use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Quarto;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class InternacaoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'paciente_id'           => Paciente::factory(),
            'quarto_id'             => Quarto::factory(),
            'medico_responsavel_id' => Medico::factory(),
            'data_entrada'          => Carbon::instance(fake()->dateTimeBetween('-30 days', '-1 day')),
            'data_saida'            => fn (array $a) => fake()->boolean(60)
                ? Carbon::parse($a['data_entrada'])->addDays(fake()->numberBetween(1, 10))
                : null,
            'diagnostico'           => fake()->randomElement([
                'Pneumonia', 'Pós-operatório de apendicectomia', 'Insuficiência cardíaca',
                'Fratura de fêmur', 'Infecção urinária',
            ]),
        ];
    }
}
