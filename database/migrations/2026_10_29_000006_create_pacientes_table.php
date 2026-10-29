<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('cpf', 14)->unique();
            $table->date('data_nascimento');
            $table->char('sexo', 1)->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('endereco', 200)->nullable();
            $table->foreignId('convenio_id')->nullable()->constrained('convenios');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE pacientes ADD CONSTRAINT pacientes_sexo_check CHECK (sexo IN ('M','F','O'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
