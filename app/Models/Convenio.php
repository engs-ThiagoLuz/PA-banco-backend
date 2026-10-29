<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Convenio extends Model
{
    protected $table = 'convenios';

    protected $fillable = ['nome', 'telefone'];

    public function pacientes(): HasMany
    {
        return $this->hasMany(Paciente::class);
    }
}
