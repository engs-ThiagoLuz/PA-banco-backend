<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('crm', 20)->unique();
            $table->foreignId('especialidade_id')->constrained('especialidades');
            $table->string('telefone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->date('data_contratacao')->default(DB::raw('CURRENT_DATE'));
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicos');
    }
};
