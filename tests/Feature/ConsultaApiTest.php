<?php

namespace Tests\Feature;

use App\Models\Consulta;
use App\Models\Medico;
use App\Models\Paciente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_consultas_retorna_200_com_estrutura_json_esperada(): void
    {
        Consulta::factory()->count(3)->create();

        $response = $this->getJson('/api/consultas');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'data_hora',
                        'status',
                        'motivo',
                        'valor',
                        'paciente' => ['id', 'nome'],
                        'medico'   => ['id', 'nome', 'crm', 'especialidade'],
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_post_consultas_cria_registro_e_retorna_201(): void
    {
        $paciente = Paciente::factory()->create();
        $medico = Medico::factory()->create();

        $response = $this->postJson('/api/consultas', [
            'paciente_id' => $paciente->id,
            'medico_id'   => $medico->id,
            'data_hora'   => now()->addDays(3)->setTime(9, 0, 0)->format('Y-m-d\TH:i'),
            'motivo'      => 'Check-up anual',
            'valor'       => 300,
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'data_hora', 'status', 'motivo', 'valor', 'paciente', 'medico'],
            ])
            ->assertJsonPath('data.status', 'Agendada')
            ->assertJsonPath('data.paciente.id', $paciente->id)
            ->assertJsonPath('data.medico.id', $medico->id);

        $this->assertDatabaseHas('consultas', [
            'id'     => $response->json('data.id'),
            'motivo' => 'Check-up anual',
        ]);
    }

    public function test_post_consultas_com_dados_invalidos_retorna_422(): void
    {
        $response = $this->postJson('/api/consultas', [
            'paciente_id' => 999999,
            'status'      => 'Inexistente',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['paciente_id', 'medico_id', 'data_hora', 'status']);

        $this->assertDatabaseCount('consultas', 0);
    }

    public function test_delete_consultas_remove_registro_e_retorna_204(): void
    {
        $consulta = Consulta::factory()->create();

        $this->deleteJson("/api/consultas/{$consulta->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('consultas', ['id' => $consulta->id]);
    }
}
