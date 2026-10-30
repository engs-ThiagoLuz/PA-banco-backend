<?php

namespace Database\Factories;

use App\Models\Medico;
use App\Models\Paciente;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ConsultaFactory extends Factory
{
    public function definition(): array
    {
        $dataHora = $this->horarioComercial(fake()->dateTimeBetween('-60 days', '+30 days'));

        return [
            'paciente_id' => Paciente::factory(),
            'medico_id'   => Medico::factory(),
            'data_hora'   => $dataHora,
            'status'      => $dataHora->isFuture()
                ? 'Agendada'
                : fake()->randomElement(['Realizada', 'Realizada', 'Realizada', 'Cancelada']),
            'motivo'      => fake()->randomElement([
                'Consulta de rotina', 'Retorno', 'Dor de cabeça frequente', 'Check-up anual',
                'Dor lombar', 'Acompanhamento de exames', 'Avaliação pré-operatória',
            ]),
            'valor'       => fake()->randomElement([150, 200, 250, 300, 400]),
        ];
    }

    public function agendada(): static
    {
        return $this->state(fn () => [
            'status'    => 'Agendada',
            'data_hora' => $this->horarioComercial(fake()->dateTimeBetween('+1 day', '+30 days')),
        ]);
    }

    public function realizada(): static
    {
        return $this->state(fn () => [
            'status'    => 'Realizada',
            'data_hora' => $this->horarioComercial(fake()->dateTimeBetween('-60 days', '-1 day')),
        ]);
    }

    public function cancelada(): static
    {
        return $this->state(fn () => ['status' => 'Cancelada']);
    }

    private function horarioComercial(DateTimeInterface $data): Carbon
    {
        return Carbon::instance($data)->setTime(
            fake()->numberBetween(8, 17),
            fake()->randomElement([0, 30]),
            0
        );
    }
}
