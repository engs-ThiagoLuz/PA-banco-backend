<?php

namespace App\Http\Requests;

use App\Services\ConsultaService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConsultaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paciente_id' => ['required', 'integer', 'exists:pacientes,id'],
            'medico_id'   => ['required', 'integer', 'exists:medicos,id'],
            'data_hora'   => ['required', 'date'],
            'status'      => ['sometimes', Rule::in(ConsultaService::STATUS)],
            'motivo'      => ['nullable', 'string', 'max:200'],
            'valor'       => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'exists'   => 'O registro selecionado em :attribute não existe.',
            'date'     => 'O campo :attribute deve ser uma data válida.',
            'in'       => 'O valor informado em :attribute é inválido.',
            'numeric'  => 'O campo :attribute deve ser numérico.',
            'max'      => 'O campo :attribute excede o tamanho máximo permitido.',
            'min'      => 'O campo :attribute não pode ser negativo.',
        ];
    }

    public function attributes(): array
    {
        return [
            'paciente_id' => 'paciente',
            'medico_id'   => 'médico',
            'data_hora'   => 'data e hora',
        ];
    }
}
