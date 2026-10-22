<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recetas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 150);
            $table->unsignedInteger('porciones');
            $table->text('instrucciones');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recetas');
    }
};