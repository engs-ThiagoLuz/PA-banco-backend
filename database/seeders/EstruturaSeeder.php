<?php

namespace Database\Seeders;

use App\Models\Departamento;
use App\Models\Quarto;
use Illuminate\Database\Seeder;

class EstruturaSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Departamento::all() as $indice => $departamento) {
            $ehUti = str_contains($departamento->nome, 'UTI');

            for ($i = 1; $i <= 6; $i++) {
                $numero = ($indice + 1) . str_pad((string) $i, 2, '0', STR_PAD_LEFT);

                $tipo = $ehUti ? 'UTI' : ($i <= 3 ? 'Enfermaria' : 'Apartamento');
                $capacidade = match ($tipo) {
                    'Enfermaria' => 4,
                    default      => 1,
                };

                Quarto::firstOrCreate(
                    ['numero' => $numero, 'departamento_id' => $departamento->id],
                    ['tipo' => $tipo, 'capacidade' => $capacidade]
                );
            }
        }
    }
}
