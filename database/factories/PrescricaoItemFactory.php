<?php

namespace Database\Factories;

use App\Models\Medicamento;
use App\Models\Prescricao;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrescricaoItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'prescricao_id'  => Prescricao::factory(),
            'medicamento_id' => Medicamento::factory(),
            'dosagem'        => fake()->randomElement(['500 mg', '250 mg', '10 mg', '1 comprimido', '5 ml']),
            'frequencia'     => fake()->randomElement(['6/6h', '8/8h', '12/12h', '1x ao dia']),
            'duracao_dias'   => fake()->randomElement([5, 7, 10, 14, 30]),
        ];
    }
}
