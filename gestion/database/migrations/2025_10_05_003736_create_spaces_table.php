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
        Schema::create('spaces', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Nombre del espacio (ej: Salón de fiesta)
            $table->text('descripcion')->nullable(); // Descripción del espacio
            $table->decimal('precio_por_dia', 10, 2); // Precio por día de alquiler
            $table->boolean('activo')->default(true); // Si el espacio está disponible para reservas
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spaces');
    }
};
