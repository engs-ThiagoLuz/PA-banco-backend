<?php

namespace App\Http\Controllers;

use App\Http\Requests\RelatorioRequest;
use App\Models\Departamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RelatorioController extends Controller
{
    public function index(RelatorioRequest $request): View
    {
        return view('relatorios.index', [
            'faturamento' => $this->dadosFaturamento($request),
            'ocupacao' => $this->dadosOcupacao($request),
            'departamentos' => Departamento::orderBy('nome')->get(['id', 'nome']),
            'filtros' => $request->validated(),
        ]);
    }

    public function faturamento(RelatorioRequest $request): JsonResponse
    {
        return response()->json(['data' => $this->dadosFaturamento($request)]);
    }

    public function ocupacao(RelatorioRequest $request): JsonResponse
    {
        return response()->json(['data' => $this->dadosOcupacao($request)]);
    }

    private function dadosFaturamento(RelatorioRequest $request): array
    {
        return DB::select("SELECT * FROM fn_faturamento_por_medico(COALESCE(CAST(? AS DATE), date_trunc('month', CURRENT_DATE)::date), COALESCE(CAST(? AS DATE), CURRENT_DATE))", [
            $request->validated('data_inicio'), $request->validated('data_fim'),
        ]);
    }

    private function dadosOcupacao(RelatorioRequest $request): array
    {
        return DB::select('SELECT * FROM fn_ocupacao_quartos(CAST(? AS BIGINT))', [$request->validated('departamento_id')]);
    }
}
