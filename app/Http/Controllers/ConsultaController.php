<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConsultaRequest;
use App\Models\Consulta;
use App\Models\Medico;
use App\Models\Paciente;
use App\Services\ConsultaService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsultaController extends Controller
{
    public function __construct(private ConsultaService $service)
    {
    }

    public function index(Request $request): View
    {
        $consultas = $this->service->listar($request->only(['busca', 'status']));

        return view('consultas.index', [
            'consultas'  => $consultas,
            'statusList' => ConsultaService::STATUS,
        ]);
    }

    public function create(): View
    {
        return view('consultas.create', $this->dadosFormulario());
    }

    public function store(ConsultaRequest $request): RedirectResponse
    {
        $this->service->criar($request->validated());

        return redirect()->route('consultas.index')
            ->with('sucesso', 'Consulta agendada com sucesso.');
    }

    public function show(Consulta $consulta): View
    {
        $consulta->load([
            'paciente.convenio',
            'medico.especialidade',
            'prontuarios',
            'prescricoes.itens.medicamento',
        ]);

        return view('consultas.show', compact('consulta'));
    }

    public function edit(Consulta $consulta): View
    {
        return view('consultas.edit', ['consulta' => $consulta] + $this->dadosFormulario());
    }

    public function update(ConsultaRequest $request, Consulta $consulta): RedirectResponse
    {
        $this->service->atualizar($consulta, $request->validated());

        return redirect()->route('consultas.show', $consulta)
            ->with('sucesso', 'Consulta atualizada com sucesso.');
    }

    public function destroy(Consulta $consulta): RedirectResponse
    {
        try {
            $this->service->excluir($consulta);
        } catch (DomainException $e) {
            return redirect()->route('consultas.index')->with('erro', $e->getMessage());
        }

        return redirect()->route('consultas.index')
            ->with('sucesso', 'Consulta excluída com sucesso.');
    }

    private function dadosFormulario(): array
    {
        return [
            'pacientes'  => Paciente::orderBy('nome')->get(['id', 'nome']),
            'medicos'    => Medico::with('especialidade:id,nome')->orderBy('nome')->get(['id', 'nome', 'especialidade_id']),
            'statusList' => ConsultaService::STATUS,
        ];
    }
}
