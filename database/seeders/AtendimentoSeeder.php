<?php

namespace Database\Seeders;

use App\Models\Consulta;
use App\Models\Internacao;
use App\Models\Medicamento;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Prescricao;
use App\Models\PrescricaoItem;
use App\Models\Prontuario;
use App\Models\Quarto;
use Illuminate\Database\Seeder;

class AtendimentoSeeder extends Seeder
{
    public function run(): void
    {
        if (Consulta::exists()) {
            return;
        }

        $pacientes = Paciente::pluck('id');
        $medicos = Medico::pluck('id');
        $quartos = Quarto::pluck('id');
        $medicamentos = Medicamento::pluck('id');

        // 60 consultas, sem conflito de horário para o mesmo médico
        // (regra do ConsultaService e, futuramente, da trigger do banco).
        $usados = [];
        $criadas = 0;

        while ($criadas < 60) {
            $dados = Consulta::factory()->raw([
                'paciente_id' => $pacientes->random(),
                'medico_id'   => $medicos->random(),
            ]);

            $chave = $dados['medico_id'] . '|' . $dados['data_hora']->format('Y-m-d H:i');
            if (isset($usados[$chave])) {
                continue;
            }

            $usados[$chave] = true;
            Consulta::create($dados);
            $criadas++;
        }

        // Prontuário para cada consulta realizada; prescrição para cerca de metade delas.
        foreach (Consulta::where('status', 'Realizada')->get() as $consulta) {
            Prontuario::factory()->create([
                'consulta_id'   => $consulta->id,
                'paciente_id'   => $consulta->paciente_id,
                'data_registro' => $consulta->data_hora,
            ]);

            if (! fake()->boolean(50)) {
                continue;
            }

            $prescricao = Prescricao::factory()->create([
                'consulta_id'  => $consulta->id,
                'medico_id'    => $consulta->medico_id,
                'paciente_id'  => $consulta->paciente_id,
                'data_emissao' => $consulta->data_hora->toDateString(),
            ]);

            foreach ($medicamentos->random(random_int(1, 3)) as $medicamentoId) {
                PrescricaoItem::factory()->create([
                    'prescricao_id'  => $prescricao->id,
                    'medicamento_id' => $medicamentoId,
                ]);
            }
        }

        // 8 internações
        for ($i = 0; $i < 8; $i++) {
            Internacao::factory()->create([
                'paciente_id'           => $pacientes->random(),
                'quarto_id'             => $quartos->random(),
                'medico_responsavel_id' => $medicos->random(),
            ]);
        }
    }
}
