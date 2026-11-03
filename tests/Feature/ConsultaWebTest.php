<?php

namespace Tests\Feature;

use App\Models\Consulta;
use App\Models\Medico;
use App\Models\Paciente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultaWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_listagem_exibe_as_consultas_cadastradas(): void
    {
        $consulta = Consulta::factory()->agendada()->create();

        $response = $this->get(route('consultas.index'));

        $response->assertOk()
            ->assertViewIs('consultas.index')
            ->assertViewHas('consultas')
            ->assertSee($consulta->paciente->nome)
            ->assertSee($consulta->medico->nome)
            ->assertSee('Agendada');
    }

    public function test_cadastro_de_consulta_grava_no_banco_e_redireciona(): void
    {
        $paciente = Paciente::factory()->create();
        $medico = Medico::factory()->create();
        $dataHora = now()->addDays(5)->setTime(14, 30, 0);

        $response = $this->post(route('consultas.store'), [
            'paciente_id' => $paciente->id,
            'medico_id'   => $medico->id,
            'data_hora'   => $dataHora->format('Y-m-d\TH:i'),
            'motivo'      => 'Retorno',
            'valor'       => 250.00,
        ]);

        $response->assertRedirect(route('consultas.index'))
            ->assertSessionHas('sucesso');

        $this->assertDatabaseHas('consultas', [
            'paciente_id' => $paciente->id,
            'medico_id'   => $medico->id,
            'data_hora'   => $dataHora->format('Y-m-d H:i:s'),
            'status'      => 'Agendada',
            'motivo'      => 'Retorno',
        ]);
    }

    public function test_cadastro_rejeita_consulta_no_mesmo_horario_do_medico(): void
    {
        $existente = Consulta::factory()->agendada()->create();

        $response = $this->from(route('consultas.create'))->post(route('consultas.store'), [
            'paciente_id' => Paciente::factory()->create()->id,
            'medico_id'   => $existente->medico_id,
            'data_hora'   => $existente->data_hora->format('Y-m-d\TH:i'),
        ]);

        $response->assertRedirect(route('consultas.create'))
            ->assertSessionHasErrors('data_hora');

        $this->assertDatabaseCount('consultas', 1);
    }
}
