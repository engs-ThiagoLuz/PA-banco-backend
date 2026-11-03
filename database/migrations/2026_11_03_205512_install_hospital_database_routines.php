<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $sql = file_get_contents(database_path('sql/PA-Scriptsql.sql'));

        // Keep the schema selected by Laravel instead of the script's public override.
        $sql = str_replace('SET search_path TO public;', '', $sql);
        // PostgreSQL cannot replace a procedure when an earlier version used different parameter names.
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_cancelar_consulta(BIGINT, TEXT); DROP PROCEDURE IF EXISTS sp_realizar_consulta(BIGINT, TEXT, VARCHAR);');
        DB::unprepared($sql);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            DROP TRIGGER IF EXISTS trg_consultas_conflito_horario ON consultas;
            DROP TRIGGER IF EXISTS trg_consultas_log_status ON consultas;
            DROP PROCEDURE IF EXISTS sp_cancelar_consulta(BIGINT, TEXT);
            DROP PROCEDURE IF EXISTS sp_realizar_consulta(BIGINT, TEXT, VARCHAR);
            DROP FUNCTION IF EXISTS fn_valida_conflito_consulta();
            DROP FUNCTION IF EXISTS fn_log_status_consulta();
            DROP FUNCTION IF EXISTS fn_faturamento_por_medico(DATE, DATE);
            DROP FUNCTION IF EXISTS fn_ocupacao_quartos(BIGINT);
            DROP TABLE IF EXISTS consultas_status_log;
            DROP INDEX IF EXISTS idx_pacientes_nome_trgm;
            DROP INDEX IF EXISTS idx_pacientes_cpf_trgm;
            DROP INDEX IF EXISTS idx_medicos_nome_trgm;
            DROP INDEX IF EXISTS idx_consultas_paciente_data;
            DROP INDEX IF EXISTS idx_internacoes_quarto_ativa;
            SQL);
    }
};
