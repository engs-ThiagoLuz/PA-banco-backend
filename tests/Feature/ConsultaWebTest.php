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

    public function test_resumo_exibe_totais_do_banco_e_filtros_mantem_contagem_global(): void
    {
        Consulta::factory()->agendada()->create();
        Consulta::factory()->realizada()->create();
        Consulta::factory()->cancelada()->create();

        $this->get(route('consultas.index', ['status' => 'Agendada']))
            ->assertOk()
            ->assertViewHas('resumo', ['total' => 3, 'agendadas' => 1, 'realizadas' => 1, 'canceladas' => 1])
            ->assertViewHas('consultas', fn ($consultas) => $consultas->total() === 1)
            ->assertSee('Agenda de atendimentos');
    }

    public function test_busca_medico_com_status_e_periodo_inclui_o_ultimo_dia_sem_vazar_outros_registros(): void
    {
        $medico = Medico::factory()->create(['nome' => 'Dra. Helena Integração']);
        $paciente = Paciente::factory()->create();
        foreach ([
            ['2026-11-01 00:00:00', 'Agendada'],
            ['2026-11-03 23:59:59', 'Agendada'],
            ['2026-10-31 23:59:59', 'Agendada'],
            ['2026-11-04 00:00:00', 'Agendada'],
            ['2026-11-02 10:00:00', 'Cancelada'],
        ] as [$data, $status]) {
            Consulta::factory()->create(['medico_id' => $medico->id, 'paciente_id' => $paciente->id, 'data_hora' => $data, 'status' => $status]);
        }

        $this->get(route('consultas.index', [
            'busca' => 'helena integração', 'medico_id' => $medico->id,
            'status' => 'Agendada', 'data_inicio' => '2026-11-01', 'data_fim' => '2026-11-03',
        ]))->assertOk()
            ->assertViewHas('consultas', fn ($consultas) => $consultas->total() === 2)
            ->assertSee('03/11/2026')
            ->assertDontSee('31/10/2026');
    }

    public function test_busca_por_cpf_exibe_convenio_e_escapa_nome_do_paciente(): void
    {
        $paciente = Paciente::factory()->create(['nome' => '<script>alert(1)</script>', 'cpf' => '123.456.789-01']);
        $consulta = Consulta::factory()->create(['paciente_id' => $paciente->id]);

        $this->get(route('consultas.index', ['busca' => '123.456.789-01']))->assertOk()
            ->assertSee($paciente->nome)
            ->assertDontSee($paciente->nome, false)
            ->assertSee($paciente->convenio?->nome ?? 'Sem convênio')
            ->assertViewHas('consultas', fn ($consultas) => $consultas->total() === 1 && $consultas->first()->id === $consulta->id);
    }

    public function test_paginacao_preserva_filtros_e_ordena_horarios_iguais_pelo_id(): void
    {
        $paciente = Paciente::factory()->create();
        $medico = Medico::factory()->create();
        $consultas = Consulta::factory()->count(11)->create([
            'paciente_id' => $paciente->id, 'medico_id' => $medico->id,
            'data_hora' => '2026-11-03 09:00:00', 'status' => 'Agendada',
        ]);

        $this->get(route('consultas.index', ['status' => 'Agendada', 'por_pagina' => 10, 'page' => 2]))
            ->assertOk()
            ->assertViewHas('consultas', fn ($pagina) => $pagina->total() === 11
                && $pagina->first()->id === $consultas->first()->id
                && str_contains($pagina->previousPageUrl(), 'status=Agendada'));
    }

    public function test_filtros_invalidos_retornam_erros_de_validacao(): void
    {
        $this->from(route('consultas.index'))->get(route('consultas.index', [
            'data_inicio' => '2026-11-03', 'data_fim' => '2026-11-01',
            'status' => 'Inválido', 'medico_id' => 999999, 'por_pagina' => 10000,
        ]))->assertRedirect(route('consultas.index'))
            ->assertSessionHasErrors(['data_fim', 'status', 'medico_id', 'por_pagina'])
            ->assertSessionHasErrors(['data_fim' => 'A data final deve ser igual ou posterior à data inicial.']);
    }

    public function test_edicao_atualiza_registro_no_banco_e_exibe_os_detalhes(): void
    {
        $consulta = Consulta::factory()->agendada()->create();

        $this->put(route('consultas.update', $consulta), [
            'paciente_id' => $consulta->paciente_id, 'medico_id' => $consulta->medico_id,
            'data_hora' => $consulta->data_hora->format('Y-m-d H:i:s'),
            'status' => 'Cancelada', 'motivo' => 'Cancelamento solicitado pelo paciente', 'valor' => 275.50,
        ])->assertRedirect(route('consultas.show', $consulta))->assertSessionHas('sucesso');

        $this->assertDatabaseHas('consultas', ['id' => $consulta->id, 'status' => 'Cancelada', 'valor' => 275.50]);
        $this->get(route('consultas.show', $consulta))->assertOk()->assertSee('Cancelamento solicitado pelo paciente');
    }

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
            'medico_id' => $medico->id,
            'data_hora' => $dataHora->format('Y-m-d\TH:i'),
            'motivo' => 'Retorno',
            'valor' => 250.00,
        ]);

        $response->assertRedirect(route('consultas.index'))
            ->assertSessionHas('sucesso');

        $this->assertDatabaseHas('consultas', [
            'paciente_id' => $paciente->id,
            'medico_id' => $medico->id,
            'data_hora' => $dataHora->format('Y-m-d H:i:s'),
            'status' => 'Agendada',
            'motivo' => 'Retorno',
        ]);
    }

    public function test_cadastro_rejeita_consulta_no_mesmo_horario_do_medico(): void
    {
        $existente = Consulta::factory()->agendada()->create();

        $response = $this->from(route('consultas.create'))->post(route('consultas.store'), [
            'paciente_id' => Paciente::factory()->create()->id,
            'medico_id' => $existente->medico_id,
            'data_hora' => $existente->data_hora->format('Y-m-d\TH:i'),
        ]);

        $response->assertRedirect(route('consultas.create'))
            ->assertSessionHasErrors('data_hora');

        $this->assertDatabaseCount('consultas', 1);
    }
}
