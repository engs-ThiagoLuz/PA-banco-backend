<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescricoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consulta_id')->nullable()->constrained('consultas');
            $table->foreignId('medico_id')->constrained('medicos');
            $table->foreignId('paciente_id')->constrained('pacientes');
            $table->date('data_emissao')->default(DB::raw('CURRENT_DATE'));
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescricoes');
    }
};
