<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescricao extends Model
{
    protected $table = 'prescricoes';

    protected $fillable = ['consulta_id', 'medico_id', 'paciente_id', 'data_emissao'];

    protected function casts(): array
    {
        return ['data_emissao' => 'date'];
    }

    public function consulta(): BelongsTo
    {
        return $this->belongsTo(Consulta::class);
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class);
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function itens(): HasMany
    {
        return $this->hasMany(PrescricaoItem::class, 'prescricao_id');
    }
}
