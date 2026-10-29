<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quartos', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 10);
            $table->string('tipo', 30);
            $table->integer('capacidade')->default(1);
            $table->foreignId('departamento_id')->constrained('departamentos');
            $table->timestamps();

            $table->unique(['numero', 'departamento_id']);
        });

        DB::statement("ALTER TABLE quartos ADD CONSTRAINT quartos_tipo_check CHECK (tipo IN ('Enfermaria','Apartamento','UTI'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('quartos');
    }
};
