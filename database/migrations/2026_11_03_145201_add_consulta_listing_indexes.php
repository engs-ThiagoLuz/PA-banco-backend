<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->index(['data_hora', 'id']);
            $table->index(['status', 'data_hora', 'id']);
            $table->index(['medico_id', 'data_hora']);
        });
        Schema::table('prontuarios', fn (Blueprint $table) => $table->index('consulta_id'));
        Schema::table('prescricoes', fn (Blueprint $table) => $table->index('consulta_id'));
        Schema::table('prescricao_itens', fn (Blueprint $table) => $table->index('prescricao_id'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->dropIndex(['data_hora', 'id']);
            $table->dropIndex(['status', 'data_hora', 'id']);
            $table->dropIndex(['medico_id', 'data_hora']);
        });
        Schema::table('prontuarios', fn (Blueprint $table) => $table->dropIndex(['consulta_id']));
        Schema::table('prescricoes', fn (Blueprint $table) => $table->dropIndex(['consulta_id']));
        Schema::table('prescricao_itens', fn (Blueprint $table) => $table->dropIndex(['prescricao_id']));
    }
};
