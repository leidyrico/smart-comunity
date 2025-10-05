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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartamento_id')->constrained('apartamentos')->onDelete('cascade');
            $table->foreignId('space_id')->constrained('spaces')->onDelete('cascade');
            $table->date('fecha_reserva'); // Fecha de la reserva
            $table->decimal('monto', 10, 2); // Monto del alquiler
            $table->enum('estado', ['pendiente', 'confirmada', 'cancelada'])->default('pendiente');
            $table->text('observaciones')->nullable(); // Observaciones adicionales
            $table->foreignId('recibo_id')->nullable()->constrained('recibo_gasto_comuns')->onDelete('set null'); // Relación con el recibo generado
            $table->timestamps();
            
            // Índice único para evitar dobles reservas del mismo espacio en la misma fecha
            $table->unique(['space_id', 'fecha_reserva']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
