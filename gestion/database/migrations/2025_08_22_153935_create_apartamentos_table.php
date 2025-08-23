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
        Schema::create('apartamentos', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique(); // Número del apartamento (ej: 101, 201A)
            $table->integer('piso')->nullable(); // Piso del apartamento
            $table->string('torre')->nullable(); // Torre o bloque (ej: A, B, Norte)
            $table->string('propietario'); // Nombre del propietario
            $table->string('telefono')->nullable(); // Teléfono de contacto
            $table->string('email')->nullable(); // Email de contacto
            $table->decimal('area_m2', 8, 2)->nullable(); // Área en metros cuadrados
            $table->enum('tipo', ['apartamento', 'local', 'parqueadero', 'deposito'])->default('apartamento');
            $table->enum('estado', ['ocupado', 'desocupado', 'en_arriendo'])->default('ocupado');
            $table->text('observaciones')->nullable(); // Observaciones adicionales
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartamentos');
    }
};
