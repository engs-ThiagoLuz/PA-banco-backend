<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paciente extends Model
{
    protected $table = 'pacientes';

    protected $fillable = [
        'nome', 'cpf', 'data_nascimento', 'sexo', 'telefone', 'email', 'endereco', 'convenio_id',
    ];

    protected function casts(): array
    {
        return ['data_nascimento' => 'date'];
    }

    public function convenio(): BelongsTo
    {
        return $this->belongsTo(Convenio::class);
    }

    public function consultas(): HasMany
    {
        return $this->hasMany(Consulta::class);
    }

    public function prontuarios(): HasMany
    {
        return $this->hasMany(Prontuario::class);
    }

    public function internacoes(): HasMany
    {
        return $this->hasMany(Internacao::class);
    }

    public function prescricoes(): HasMany
    {
        return $this->hasMany(Prescricao::class);
    }
}
