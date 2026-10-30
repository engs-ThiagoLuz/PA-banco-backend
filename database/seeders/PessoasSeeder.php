<?php

namespace Database\Seeders;

use App\Models\Convenio;
use App\Models\Especialidade;
use App\Models\Medico;
use App\Models\Paciente;
use Illuminate\Database\Seeder;

class PessoasSeeder extends Seeder
{
    public function run(): void
    {
        // Só popula se ainda não houver médicos/pacientes (evita duplicar ao rodar duas vezes).
        if (Medico::exists() || Paciente::exists()) {
            return;
        }

        $especialidades = Especialidade::pluck('id');
        $convenios = Convenio::pluck('id');

        Medico::factory()
            ->count(10)
            ->state(fn () => ['especialidade_id' => $especialidades->random()])
            ->create();

        Paciente::factory()
            ->count(40)
            ->state(fn () => [
                'convenio_id' => fake()->boolean(60) ? $convenios->random() : null,
            ])
            ->create();
    }
}
