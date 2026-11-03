<?php

namespace Tests\Feature;

use App\Models\Consulta;
use App\Models\Especialidade;
use App\Services\HospitalDatasetImporter;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class HospitalDatasetImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplied_dataset_loads_with_relationships_and_can_be_seeded_twice(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('especialidades', 15);
        $this->assertDatabaseCount('convenios', 8);
        $this->assertDatabaseCount('departamentos', 8);
        $this->assertDatabaseCount('medicamentos', 40);
        $this->assertDatabaseCount('medicos', 80);
        $this->assertDatabaseCount('pacientes', 4000);
        $this->assertDatabaseCount('quartos', 111);
        $this->assertDatabaseCount('consultas', 40000);
        $this->assertDatabaseCount('consultas_status_log', 0);
        $this->assertDatabaseCount('prontuarios', 23984);
        $this->assertDatabaseCount('internacoes', 4000);
        $this->assertDatabaseCount('prescricoes', 15000);
        $this->assertDatabaseCount('prescricao_itens', 37544);
        $this->assertDatabaseHas('medicos', ['id' => 1, 'especialidade_id' => 12]);
        $this->assertSame('Clínica Geral', DB::table('especialidades')->where('id', 12)->value('nome'));

        $consulta = Consulta::has('prescricoes.itens')->firstOrFail();
        $this->get(route('consultas.show', $consulta))->assertOk()
            ->assertSee($consulta->paciente->nome)
            ->assertSee($consulta->prescricoes->first()->itens->first()->medicamento->nome);
        $this->getJson('/api/consultas?busca=100.000.000-00')->assertOk()
            ->assertJsonPath('data.0.paciente.nome', 'João Santos Castro');

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('consultas', 40000);
        $this->assertDatabaseCount('hospital_dataset_imports', 1);
        $this->assertSame(16, Especialidade::create(['nome' => 'Nova especialidade'])->id);
        $novaConsulta = Consulta::factory()->agendada()->create();
        $this->assertSame(40001, $novaConsulta->id);
        $this->assertDatabaseHas('consultas_status_log', ['consulta_id' => $novaConsulta->id, 'status_novo' => 'Agendada']);
    }

    public function test_import_refuses_existing_records_without_changing_them(): void
    {
        $especialidade = Especialidade::factory()->create();

        try {
            app(HospitalDatasetImporter::class)->import(database_path('sql/inserts_sh_bd.sql'));
            $this->fail('Import should refuse populated hospital tables.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Import requires empty hospital tables', $exception->getMessage());
        }

        $this->assertModelExists($especialidade);
        $this->assertDatabaseCount('especialidades', 1);
        $this->assertDatabaseCount('hospital_dataset_imports', 0);
    }

    public function test_invalid_relationship_rolls_back_every_insert(): void
    {
        try {
            app(HospitalDatasetImporter::class)->import(base_path('tests/Fixtures/invalid_hospital.sql'));
            $this->fail('Import deve rejeitar uma foreign key.');
        } catch (QueryException $exception) {
            $this->assertSame('23503', $exception->errorInfo[0]);
        }

        $this->assertDatabaseCount('especialidades', 0);
        $this->assertDatabaseCount('medicos', 0);
        $this->assertDatabaseCount('hospital_dataset_imports', 0);
    }
}
