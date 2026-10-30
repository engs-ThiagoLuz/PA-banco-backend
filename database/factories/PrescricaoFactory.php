<?php

namespace Database\Factories;

use App\Models\Consulta;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrescricaoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'consulta_id'  => Consulta::factory()->realizada(),
            'medico_id'    => fn (array $a) => Consulta::find($a['consulta_id'])->medico_id,
            'paciente_id'  => fn (array $a) => Consulta::find($a['consulta_id'])->paciente_id,
            'data_emissao' => fn (array $a) => Consulta::find($a['consulta_id'])->data_hora->toDateString(),
        ];
    }
}
