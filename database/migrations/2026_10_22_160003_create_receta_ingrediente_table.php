<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receta_ingrediente', function (Blueprint $table) {
            $table->foreignId('receta_id')->constrained('recetas')->cascadeOnDelete();
            $table->foreignId('ingrediente_id')->constrained('ingredientes')->cascadeOnDelete();
            $table->decimal('cantidad', 8, 2);
            $table->string('unidad', 20);

            // Un ingrediente no puede repetirse dentro de la misma receta
            $table->primary(['receta_id', 'ingrediente_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receta_ingrediente');
    }
};