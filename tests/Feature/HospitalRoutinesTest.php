<?php

namespace Tests\Feature;

use App\Models\Consulta;
use App\Models\Internacao;
use App\Models\Quarto;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HospitalRoutinesTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_substitui_procedures_legadas_com_outros_nomes_de_parametros(): void
    {
        $migration = require database_path('migrations/2026_11_03_205512_install_hospital_database_routines.php');
        $migration->down();
        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE sp_cancelar_consulta(p_consulta_id BIGINT, p_justificativa TEXT DEFAULT NULL)
            LANGUAGE plpgsql AS $$ BEGIN NULL; END; $$;
            CREATE PROCEDURE sp_realizar_consulta(p_id BIGINT, p_texto TEXT, p_diagnostico VARCHAR DEFAULT NULL)
            LANGUAGE plpgsql AS $$ BEGIN NULL; END; $$;
            SQL);
        $migration->up();

        $consulta = Consulta::factory()->agendada()->create();
        $this->postJson(route('api.consultas.cancelar', $consulta))->assertOk()->assertJsonPath('data.status', 'Cancelada');
        $this->assertDatabaseHas('consultas_status_log', ['consulta_id' => $consulta->id, 'status_novo' => 'Cancelada']);
    }

    public function test_cancelamento_via_api_atualiza_motivo_e_auditoria_e_rejeita_repeticao(): void
    {
        $consulta = Consulta::factory()->agendada()->create(['motivo' => 'Retorno']);
        $this->postJson(route('api.consultas.cancelar', $consulta), ['motivo' => 'Paciente desistiu'])
            ->assertOk()->assertJsonPath('data.status', 'Cancelada');
        $this->assertDatabaseHas('consultas', ['id' => $consulta->id, 'motivo' => 'Retorno [Cancelamento: Paciente desistiu]']);
        $this->assertDatabaseHas('consultas_status_log', [
            'consulta_id' => $consulta->id, 'status_anterior' => 'Agendada', 'status_novo' => 'Cancelada',
        ]);
        $this->postJson(route('api.consultas.cancelar', $consulta))->assertUnprocessable()->assertJsonValidationErrors('status');
        $this->assertDatabaseCount('consultas_status_log', 2);
        $this->get(route('consultas.show', $consulta))->assertOk()->assertSee('Histórico de status')->assertSee('Cancelada');
    }

    public function test_realizacao_web_registra_prontuario_e_nao_permite_nova_transicao(): void
    {
        $consulta = Consulta::factory()->agendada()->create();
        $this->post(route('consultas.realizar', $consulta), ['descricao' => 'Paciente estável.', 'diagnostico' => 'Retorno'])
            ->assertRedirect(route('consultas.show', $consulta))->assertSessionHas('sucesso');
        $this->assertDatabaseHas('prontuarios', [
            'consulta_id' => $consulta->id, 'paciente_id' => $consulta->paciente_id,
            'descricao' => 'Paciente estável.', 'diagnostico' => 'Retorno',
        ]);
        $this->assertDatabaseHas('consultas_status_log', ['consulta_id' => $consulta->id, 'status_novo' => 'Realizada']);
        $this->postJson(route('api.consultas.realizar', $consulta), ['descricao' => 'Duplicada'])
            ->assertUnprocessable()->assertJsonValidationErrors('status');
        $this->assertDatabaseCount('prontuarios', 1);
        $this->from(route('consultas.show', $consulta))->post(route('consultas.cancelar', $consulta))
            ->assertRedirect(route('consultas.show', $consulta))->assertSessionHasErrors('status');
    }

    public function test_realizacao_exige_descricao_e_valida_diagnostico_antes_do_call(): void
    {
        $consulta = Consulta::factory()->agendada()->create();
        $this->postJson(route('api.consultas.realizar', $consulta), ['diagnostico' => str_repeat('a', 201)])
            ->assertUnprocessable()->assertJsonValidationErrors(['descricao', 'diagnostico']);
        $this->assertDatabaseHas('consultas', ['id' => $consulta->id, 'status' => 'Agendada']);
        $this->assertDatabaseCount('prontuarios', 0);
    }

    public function test_falha_no_prontuario_reverte_status_e_log_da_procedure(): void
    {
        $consulta = Consulta::factory()->agendada()->create();
        try {
            DB::transaction(fn () => DB::statement('CALL sp_realizar_consulta(?, ?, ?)', [$consulta->id, 'Teste', str_repeat('a', 201)]));
            $this->fail('O banco deveria rejeitar diagnóstico longo.');
        } catch (QueryException $exception) {
            $this->assertSame('22001', $exception->errorInfo[0]);
        }
        $this->assertDatabaseHas('consultas', ['id' => $consulta->id, 'status' => 'Agendada']);
        $this->assertDatabaseCount('consultas_status_log', 1);
        $this->assertDatabaseCount('prontuarios', 0);
    }

    public function test_trigger_rejeita_conflito_de_horario_em_escrita_direta(): void
    {
        $consulta = Consulta::factory()->agendada()->create();
        try {
            DB::transaction(fn () => Consulta::factory()->agendada()->create([
                'medico_id' => $consulta->medico_id, 'data_hora' => $consulta->data_hora,
            ]));
            $this->fail('O trigger deveria rejeitar o conflito.');
        } catch (QueryException $exception) {
            $this->assertSame('P0001', $exception->errorInfo[0]);
        }
        $this->assertDatabaseCount('consultas', 1);
    }

    public function test_relatorios_usam_periodo_inclusivo_e_internacoes_ativas(): void
    {
        $consulta = Consulta::factory()->realizada()->create(['data_hora' => '2026-11-03 23:59:59', 'valor' => 275.50]);
        Consulta::factory()->cancelada()->create(['medico_id' => $consulta->medico_id, 'data_hora' => '2026-11-03 10:00:00', 'valor' => 900]);
        Consulta::factory()->realizada()->create(['medico_id' => $consulta->medico_id, 'data_hora' => '2026-11-04 00:00:00', 'valor' => 900]);
        $quarto = Quarto::factory()->create(['capacidade' => 3]);
        Internacao::factory()->create(['quarto_id' => $quarto->id, 'data_saida' => null]);
        Internacao::factory()->create(['quarto_id' => $quarto->id, 'data_entrada' => '2026-10-01', 'data_saida' => '2026-10-02']);
        Quarto::factory()->create();

        $filtros = ['data_inicio' => '2026-11-01', 'data_fim' => '2026-11-03', 'departamento_id' => $quarto->departamento_id];
        $this->getJson(route('api.relatorios.faturamento', $filtros))->assertOk()
            ->assertJsonPath('data.0.medico_id', $consulta->medico_id)
            ->assertJsonPath('data.0.total_consultas', 1)
            ->assertJsonPath('data.0.faturamento', '275.50');
        $this->getJson(route('api.relatorios.ocupacao', $filtros))->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.ocupados', 1)->assertJsonPath('data.0.vagas', 2);
        $this->get(route('relatorios.index', $filtros))->assertOk()->assertSee('275,50')->assertSee($quarto->numero);
        $this->getJson(route('api.relatorios.faturamento', ['data_inicio' => '2026-11-03', 'data_fim' => '2026-11-01']))
            ->assertUnprocessable()->assertJsonValidationErrors('data_fim');
        $this->getJson(route('api.relatorios.ocupacao', ['departamento_id' => 999999]))
            ->assertUnprocessable()->assertJsonValidationErrors('departamento_id');
    }
}
