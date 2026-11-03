<?php

namespace App\Http\Requests;

use App\Services\ConsultaService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConsultaFiltroRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'busca' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(ConsultaService::STATUS)],
            'medico_id' => ['nullable', 'integer', 'exists:medicos,id'],
            'data_inicio' => ['nullable', 'date_format:Y-m-d'],
            'data_fim' => ['nullable', 'date_format:Y-m-d', Rule::when($this->filled('data_inicio'), 'after_or_equal:data_inicio')],
            'por_pagina' => ['nullable', 'integer', Rule::in([10, 25, 50])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'data_fim.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
            'date_format' => 'Informe uma data válida no campo :attribute.',
            'exists' => 'O médico selecionado não existe.',
            'in' => 'Selecione uma opção válida para :attribute.',
            'busca.max' => 'A busca deve ter até 150 caracteres.',
        ];
    }
}
