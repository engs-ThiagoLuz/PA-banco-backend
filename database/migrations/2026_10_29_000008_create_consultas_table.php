<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes');
            $table->foreignId('medico_id')->constrained('medicos');
            $table->timestamp('data_hora');
            $table->string('status', 20)->default('Agendada');
            $table->string('motivo', 200)->nullable();
            $table->decimal('valor', 10, 2)->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE consultas ADD CONSTRAINT consultas_status_check CHECK (status IN ('Agendada','Realizada','Cancelada'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('consultas');
    }
};
