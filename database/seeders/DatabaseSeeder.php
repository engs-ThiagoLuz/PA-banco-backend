<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ApoioSeeder::class,
            EstruturaSeeder::class,
            PessoasSeeder::class,
            AtendimentoSeeder::class,
        ]);
    }
}
