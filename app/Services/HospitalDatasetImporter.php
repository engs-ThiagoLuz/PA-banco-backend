<?php

namespace App\Services;

use Generator;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use SplFileObject;

class HospitalDatasetImporter
{
    public const TABLES = [
        'especialidades', 'convenios', 'departamentos', 'medicamentos',
        'medicos', 'pacientes', 'quartos', 'consultas', 'prontuarios',
        'internacoes', 'prescricoes', 'prescricao_itens',
    ];

    /** Import a trusted, local hospital SQL dataset into the configured schema. */
    public function import(string $path): bool
    {
        $connection = DB::connection();

        if ($connection->getDriverName() !== 'pgsql') {
            throw new RuntimeException('The hospital dataset requires PostgreSQL.');
        }

        if (! is_readable($path)) {
            throw new RuntimeException('Hospital SQL dataset not found: '.$path);
        }

        $checksum = hash_file('sha256', $path);

        return $connection->transaction(function () use ($connection, $path, $checksum): bool {
            $tables = implode(', ', [...self::TABLES, 'hospital_dataset_imports']);
            $connection->statement('LOCK TABLE '.$tables.' IN ACCESS EXCLUSIVE MODE');

            if ($connection->table('hospital_dataset_imports')->where('checksum', $checksum)->exists()) {
                return false;
            }

            foreach (self::TABLES as $table) {
                if ($connection->table($table)->exists()) {
                    throw new RuntimeException('Import requires empty hospital tables; public.'.$table.' contains data. Existing records were preserved.');
                }
            }

            foreach (self::TABLES as $table) {
                $sequence = $connection->selectOne("SELECT pg_get_serial_sequence(?, 'id') AS name", [$table])->name;

                if ($sequence === null) {
                    throw new RuntimeException('Missing ID sequence for '.$table);
                }

                $connection->statement('ALTER SEQUENCE '.$connection->getQueryGrammar()->wrapTable($sequence).' RESTART WITH 1');
            }

            foreach ($this->insertStatements($path) as $statement) {
                $connection->unprepared($statement);
            }

            $connection->table('hospital_dataset_imports')->insert([
                'checksum' => $checksum,
                'imported_at' => now(),
            ]);

            foreach (self::TABLES as $table) {
                $connection->statement('ANALYZE '.$table);
            }

            return true;
        });
    }

    /**
     * Stream this dataset's multiline INSERT blocks, keeping SQL strings intact.
     * Search paths and sequence commands in the source must not override Laravel.
     *
     * @return Generator<int, string>
     */
    private function insertStatements(string $path): Generator
    {
        $statement = '';
        $inserts = 0;

        foreach (new SplFileObject($path) as $line) {
            $trimmed = trim($line);

            if ($statement === '') {
                if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                    continue;
                }

                if (preg_match('/^SET SEARCH_PATH TO \w+;$/i', $trimmed)
                    || preg_match('/^SELECT setval\(pg_get_serial_sequence\(/', $trimmed)
                    || $trimmed === 'ANALYZE;') {
                    continue;
                }

                if (! preg_match('/^INSERT INTO (\w+) \([a-z_, ]+\) VALUES$/', $trimmed, $matches)
                    || ! in_array($matches[1], self::TABLES, true)) {
                    throw new RuntimeException('Unsupported statement in hospital SQL dataset.');
                }
            }

            $statement .= $line;

            if (str_ends_with($trimmed, ';')) {
                yield $statement;
                $inserts++;
                $statement = '';
            }
        }

        if ($statement !== '' || $inserts === 0) {
            throw new RuntimeException('Hospital SQL dataset is empty or incomplete.');
        }
    }
}
