<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicamento extends Model
{
    use HasFactory;

    protected $table = 'medicamentos';

    protected $fillable = ['nome', 'fabricante', 'tipo'];

    public function prescricaoItens(): HasMany
    {
        return $this->hasMany(PrescricaoItem::class);
    }
}
