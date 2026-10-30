<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Internacao extends Model
{
    use HasFactory;

    protected $table = 'internacoes';

    protected $fillable = [
        'paciente_id', 'quarto_id', 'medico_responsavel_id',
        'data_entrada', 'data_saida', 'diagnostico',
    ];

    protected function casts(): array
    {
        return [
            'data_entrada' => 'datetime',
            'data_saida' => 'datetime',
        ];
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function quarto(): BelongsTo
    {
        return $this->belongsTo(Quarto::class);
    }

    public function medicoResponsavel(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'medico_responsavel_id');
    }
}
