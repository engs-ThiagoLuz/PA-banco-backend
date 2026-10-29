<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescricao_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescricao_id')->constrained('prescricoes')->cascadeOnDelete();
            $table->foreignId('medicamento_id')->constrained('medicamentos');
            $table->string('dosagem', 50);
            $table->string('frequencia', 50);
            $table->integer('duracao_dias')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescricao_itens');
    }
};
