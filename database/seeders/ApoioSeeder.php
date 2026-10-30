<?php

namespace Database\Seeders;

use App\Models\Convenio;
use App\Models\Departamento;
use App\Models\Especialidade;
use App\Models\Medicamento;
use Illuminate\Database\Seeder;

class ApoioSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Clínica Geral', 'Cardiologia', 'Pediatria', 'Ortopedia',
            'Dermatologia', 'Neurologia', 'Ginecologia', 'Oftalmologia',
        ] as $nome) {
            Especialidade::firstOrCreate(['nome' => $nome]);
        }

        foreach ([
            'Unimed'         => '(42) 3000-1000',
            'Bradesco Saúde' => '(42) 3000-2000',
            'SulAmérica'     => '(42) 3000-3000',
            'Amil'           => '(42) 3000-4000',
        ] as $nome => $telefone) {
            Convenio::firstOrCreate(['nome' => $nome], ['telefone' => $telefone]);
        }

        foreach ([
            'Pronto Atendimento' => 'Bloco A, térreo',
            'Clínica Médica'     => 'Bloco B, 2º andar',
            'Ortopedia'          => 'Bloco B, 3º andar',
            'Maternidade'        => 'Bloco C, 1º andar',
            'UTI Adulto'         => 'Bloco D, 4º andar',
        ] as $nome => $localizacao) {
            Departamento::firstOrCreate(['nome' => $nome], ['localizacao' => $localizacao]);
        }

        foreach ([
            ['Dipirona 500 mg', 'EMS', 'Comprimido'],
            ['Paracetamol 750 mg', 'Medley', 'Comprimido'],
            ['Ibuprofeno 600 mg', 'Eurofarma', 'Comprimido'],
            ['Amoxicilina 500 mg', 'Neo Química', 'Cápsula'],
            ['Azitromicina 500 mg', 'EMS', 'Comprimido'],
            ['Omeprazol 20 mg', 'Aché', 'Cápsula'],
            ['Losartana 50 mg', 'Eurofarma', 'Comprimido'],
            ['Metformina 850 mg', 'Medley', 'Comprimido'],
            ['Dexametasona 4 mg', 'Aché', 'Comprimido'],
            ['Loratadina 10 mg', 'Neo Química', 'Comprimido'],
            ['Dipirona gotas', 'Sanofi', 'Xarope'],
            ['Diclofenaco gel', 'Sanofi', 'Pomada'],
        ] as [$nome, $fabricante, $tipo]) {
            Medicamento::firstOrCreate(
                ['nome' => $nome],
                ['fabricante' => $fabricante, 'tipo' => $tipo]
            );
        }
    }
}
