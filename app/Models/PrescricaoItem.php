<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescricaoItem extends Model
{
    protected $table = 'prescricao_itens';

    protected $fillable = [
        'prescricao_id', 'medicamento_id', 'dosagem', 'frequencia', 'duracao_dias',
    ];

    public function prescricao(): BelongsTo
    {
        return $this->belongsTo(Prescricao::class);
    }

    public function medicamento(): BelongsTo
    {
        return $this->belongsTo(Medicamento::class);
    }
}
