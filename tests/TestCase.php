<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
        * Testes feitos em um banco de testes separado
     */
    protected function refreshApplication(): void
    {
        parent::refreshApplication();

        $conexao = config('database.default');
        $banco = (string) config("database.connections.{$conexao}.database");

        if (! str_contains(strtolower($banco), 'test')) {
            throw new RuntimeException(
                "Testes bloqueados: o banco '{$banco}' não parece ser de testes. " .
                'Configure DB_DATABASE no .env.testing com um banco separado (ex.: hospital_teste).'
            );
        }
    }
}
