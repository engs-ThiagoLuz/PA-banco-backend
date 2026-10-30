<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsultaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'data_hora' => $this->data_hora?->toIso8601String(),
            'status'    => $this->status,
            'motivo'    => $this->motivo,
            'valor'     => $this->valor !== null ? (float) $this->valor : null,
            'paciente'  => $this->whenLoaded('paciente', fn () => [
                'id'   => $this->paciente->id,
                'nome' => $this->paciente->nome,
            ]),
            'medico'    => $this->whenLoaded('medico', fn () => [
                'id'            => $this->medico->id,
                'nome'          => $this->medico->nome,
                'crm'           => $this->medico->crm,
                'especialidade' => $this->medico->relationLoaded('especialidade')
                    ? $this->medico->especialidade?->nome
                    : null,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
