<?php

namespace Database\Seeders;

use App\Services\HospitalDatasetImporter;
use Illuminate\Database\Seeder;

class HospitalDatasetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(HospitalDatasetImporter $importer): void
    {
        $imported = $importer->import(database_path('sql/inserts_sh_bd.sql'));

        $this->command?->info($imported ? 'Hospital dataset imported.' : 'Hospital dataset already imported; no changes made.');
    }
}
