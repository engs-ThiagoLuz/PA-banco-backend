<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConsultaRequest;
use App\Http\Resources\ConsultaResource;
use App\Models\Consulta;
use App\Services\ConsultaService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ConsultaApiController extends Controller
{
    public function __construct(private ConsultaService $service)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        return ConsultaResource::collection(
            $this->service->listar($request->only(['busca', 'status']))
        );
    }

    public function store(ConsultaRequest $request): JsonResponse
    {
        $consulta = $this->service->criar($request->validated())
            ->load(['paciente', 'medico.especialidade']);

        return (new ConsultaResource($consulta))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Consulta $consulta): ConsultaResource
    {
        return new ConsultaResource($consulta->load(['paciente', 'medico.especialidade']));
    }

    public function update(ConsultaRequest $request, Consulta $consulta): ConsultaResource
    {
        $consulta = $this->service->atualizar($consulta, $request->validated())
            ->load(['paciente', 'medico.especialidade']);

        return new ConsultaResource($consulta);
    }

    public function destroy(Consulta $consulta): JsonResponse|Response
    {
        try {
            $this->service->excluir($consulta);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_CONFLICT);
        }

        return response()->noContent();
    }
}
