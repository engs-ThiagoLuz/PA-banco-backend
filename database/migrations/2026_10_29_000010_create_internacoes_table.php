<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes');
            $table->foreignId('quarto_id')->constrained('quartos');
            $table->foreignId('medico_responsavel_id')->constrained('medicos');
            $table->timestamp('data_entrada');
            $table->timestamp('data_saida')->nullable();
            $table->string('diagnostico', 200)->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE internacoes ADD CONSTRAINT internacoes_datas_check CHECK (data_saida IS NULL OR data_saida >= data_entrada)');
    }

    public function down(): void
    {
        Schema::dropIfExists('internacoes');
    }
};
