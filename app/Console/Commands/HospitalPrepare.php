<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Facades\DB;

class HospitalPrepare extends Command
{
    use ConfirmableTrait;

    protected $signature = 'hospital:prepare {--force : Run in production without a prompt}';

    protected $description = 'Create the configured PostgreSQL schema, migrate, and import the hospital dataset';

    public function handle(): int
    {
        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $connection = DB::connection();
        $schema = $connection->getConfig('search_path');

        if ($connection->getDriverName() !== 'pgsql' || ! is_string($schema)
            || ! preg_match('/^[a-z_][a-z0-9_]*$/', $schema)) {
            $this->error('Use PostgreSQL e set DB_SCHEMA to para um schema lowercase');

            return self::FAILURE;
        }

        if (str_contains($connection->getConfig('host'), '-pooler')) {
            $this->error('Use o endpoint do Neon para criação de schema e imports.');

            return self::FAILURE;
        }

        $connection->statement('CREATE SCHEMA IF NOT EXISTS "'.$schema.'"');

        return $this->call('migrate', ['--seed' => true, '--force' => true]);
    }
}
