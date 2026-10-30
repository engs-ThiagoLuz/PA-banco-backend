<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quarto extends Model
{
    use HasFactory;

    protected $table = 'quartos';

    protected $fillable = ['numero', 'tipo', 'capacidade', 'departamento_id'];

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function internacoes(): HasMany
    {
        return $this->hasMany(Internacao::class);
    }
}
