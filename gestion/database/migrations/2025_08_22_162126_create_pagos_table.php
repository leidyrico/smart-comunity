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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartamento_id')->constrained('apartamentos')->onDelete('cascade');
            $table->foreignId('recibo_gasto_comun_id')->constrained('recibo_gasto_comuns')->onDelete('cascade');
            $table->decimal('monto_pagado', 10, 2); // Monto del pago realizado
            $table->date('fecha_pago'); // Fecha en que se realizó el pago
            $table->string('metodo_pago')->default('efectivo'); // efectivo, transferencia, cheque, etc.
            $table->string('numero_comprobante')->nullable(); // Número de comprobante o referencia
            $table->text('observaciones')->nullable(); // Observaciones del pago
            $table->enum('estado', ['confirmado', 'pendiente_confirmacion', 'rechazado'])->default('confirmado');
            $table->timestamps();
            
            // Índices para mejorar rendimiento
            $table->index(['apartamento_id', 'fecha_pago']);
            $table->index(['recibo_gasto_comun_id', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
