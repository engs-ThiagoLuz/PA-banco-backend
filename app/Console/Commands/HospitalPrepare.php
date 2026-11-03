<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Facades\DB;

class HospitalPrepare extends Command
{
    use ConfirmableTrait;

    protected $signature = 'hospital:prepare {--force : Run in production without a prompt}';

    protected $description = 'Run migrations and import the hospital dataset into the public schema';

    public function handle(): int
    {
        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $connection = DB::connection();
        if ($connection->getDriverName() !== 'pgsql') {
            $this->error('Use PostgreSQL para importar os dados no schema public.');

            return self::FAILURE;
        }

        if (str_contains($connection->getConfig('host'), '-pooler')) {
            $this->error('Use o endpoint do Neon para criação de schema e imports.');

            return self::FAILURE;
        }

        return $this->call('migrate', ['--seed' => true, '--force' => true]);
    }
}
