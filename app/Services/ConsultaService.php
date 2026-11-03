<?php

namespace App\Services;

use App\Models\Consulta;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConsultaService
{
    public const STATUS = ['Agendada', 'Realizada', 'Cancelada'];

    public function listar(array $filtros = [], int $porPagina = 10): LengthAwarePaginator
    {
        return Consulta::with(['paciente.convenio', 'medico.especialidade'])
            ->withCount(['prontuarios', 'prescricoes'])
            ->when($filtros['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filtros['medico_id'] ?? null, fn ($q, $medicoId) => $q->where('medico_id', $medicoId))
            ->when($filtros['data_inicio'] ?? null, fn ($q, $data) => $q->where('data_hora', '>=', $data.' 00:00:00'))
            ->when($filtros['data_fim'] ?? null, fn ($q, $data) => $q->where('data_hora', '<', Carbon::parse($data)->addDay()->startOfDay()))
            ->when($filtros['busca'] ?? null, function ($q, $busca) {
                $q->where(function ($query) use ($busca) {
                    $query->whereHas('paciente', fn ($p) => $p->where(function ($paciente) use ($busca) {
                        $paciente->where('nome', 'ilike', "%{$busca}%")->orWhere('cpf', 'ilike', "%{$busca}%");
                    }))->orWhereHas('medico', fn ($medico) => $medico->where('nome', 'ilike', "%{$busca}%"));
                });
            })
            ->orderByDesc('data_hora')
            ->orderByDesc('id')
            ->paginate($porPagina)
            ->withQueryString();
    }

    /** @return array{total: int, agendadas: int, realizadas: int, canceladas: int} */
    public function resumo(): array
    {
        $totais = Consulta::query()->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');

        return [
            'total' => (int) $totais->sum(),
            'agendadas' => (int) ($totais['Agendada'] ?? 0),
            'realizadas' => (int) ($totais['Realizada'] ?? 0),
            'canceladas' => (int) ($totais['Cancelada'] ?? 0),
        ];
    }

    public function criar(array $dados): Consulta
    {
        $dados['status'] ??= 'Agendada';

        $this->garantirHorarioLivre($dados['medico_id'], $dados['data_hora'], $dados['status']);

        return Consulta::create($dados)->refresh();
    }

    public function atualizar(Consulta $consulta, array $dados): Consulta
    {
        $this->garantirHorarioLivre(
            $dados['medico_id'] ?? $consulta->medico_id,
            $dados['data_hora'] ?? $consulta->data_hora,
            $dados['status'] ?? $consulta->status,
            $consulta->id,
        );

        $consulta->update($dados);

        return $consulta->refresh();
    }

    public function excluir(Consulta $consulta): void
    {
        if ($consulta->prontuarios()->exists() || $consulta->prescricoes()->exists()) {
            throw new DomainException(
                'Não é possível excluir uma consulta que possui prontuário ou prescrição. Altere o status para Cancelada.'
            );
        }

        $consulta->delete();
    }

    public function cancelar(Consulta $consulta, ?string $motivo = null): Consulta
    {
        return $this->executarAcao($consulta, 'CALL sp_cancelar_consulta(?, ?)', [$consulta->id, $motivo]);
    }

    public function realizar(Consulta $consulta, string $descricao, ?string $diagnostico = null): Consulta
    {
        return $this->executarAcao($consulta, 'CALL sp_realizar_consulta(?, ?, ?)', [$consulta->id, $descricao, $diagnostico]);
    }

    public function historico(Consulta $consulta): Collection
    {
        return DB::table('consultas_status_log')->where('consulta_id', $consulta->id)
            ->orderByDesc('alterado_em')->orderByDesc('id')->get();
    }

    private function executarAcao(Consulta $consulta, string $sql, array $parametros): Consulta
    {
        try {
            DB::transaction(fn () => DB::statement($sql, $parametros));
        } catch (QueryException $exception) {
            if (($exception->errorInfo[0] ?? null) !== 'P0001') {
                throw $exception;
            }

            throw ValidationException::withMessages([
                'status' => 'A ação não pôde ser concluída. Somente consultas agendadas podem ser canceladas ou realizadas.',
            ]);
        }

        return $consulta->refresh();
    }

    /**
     * Regra de negócio: um médico não pode ter duas consultas ativas no mesmo horário.
     */
    private function garantirHorarioLivre(int|string $medicoId, mixed $dataHora, string $status, ?int $ignorarId = null): void
    {
        if ($status === 'Cancelada') {
            return;
        }

        $conflito = Consulta::where('medico_id', $medicoId)
            ->where('data_hora', $dataHora)
            ->where('status', '!=', 'Cancelada')
            ->when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId))
            ->exists();

        if ($conflito) {
            throw ValidationException::withMessages([
                'data_hora' => 'O médico já possui uma consulta neste horário.',
            ]);
        }
    }
}
