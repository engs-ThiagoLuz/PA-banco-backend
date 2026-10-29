<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medico extends Model
{
    protected $table = 'medicos';

    protected $fillable = [
        'nome', 'crm', 'especialidade_id', 'telefone', 'email', 'data_contratacao',
    ];

    protected function casts(): array
    {
        return ['data_contratacao' => 'date'];
    }

    public function especialidade(): BelongsTo
    {
        return $this->belongsTo(Especialidade::class);
    }

    public function consultas(): HasMany
    {
        return $this->hasMany(Consulta::class);
    }

    public function internacoes(): HasMany
    {
        return $this->hasMany(Internacao::class, 'medico_responsavel_id');
    }

    public function prescricoes(): HasMany
    {
        return $this->hasMany(Prescricao::class);
    }
}
