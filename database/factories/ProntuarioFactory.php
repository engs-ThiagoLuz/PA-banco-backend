<?php

namespace Database\Factories;

use App\Models\Consulta;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProntuarioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'consulta_id'   => Consulta::factory()->realizada(),
            'paciente_id'   => fn (array $a) => Consulta::find($a['consulta_id'])->paciente_id,
            'data_registro' => fn (array $a) => Consulta::find($a['consulta_id'])->data_hora,
            'descricao'     => fake('pt_BR')->paragraph(),
            'diagnostico'   => fake()->randomElement([
                'Hipertensão arterial', 'Diabetes tipo 2', 'Rinite alérgica', 'Lombalgia',
                'Gastrite', 'Enxaqueca', 'Sem alterações',
            ]),
        ];
    }
}
