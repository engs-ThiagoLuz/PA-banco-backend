<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Trava de segurança: o RefreshDatabase executa "migrate:fresh", que apaga TODAS
     * as tabelas. Esta verificação roda ANTES disso e impede que os testes toquem
     * o banco principal (o banco de testes deve ter "test" no nome, ex.: hospital_teste).
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
