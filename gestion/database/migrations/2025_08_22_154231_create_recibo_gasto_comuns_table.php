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
        Schema::create('recibo_gasto_comuns', function (Blueprint $table) {
            $table->id();
            $table->string('numero_recibo')->unique(); // Número único del recibo
            $table->string('periodo'); // Período del recibo (ej: Enero 2025)
            $table->date('fecha_emision'); // Fecha de emisión del recibo
            $table->date('fecha_vencimiento'); // Fecha límite de pago
            $table->decimal('valor_administracion', 10, 2); // Valor administración
            $table->decimal('valor_aseo', 10, 2)->default(0); // Valor aseo
            $table->decimal('valor_vigilancia', 10, 2)->default(0); // Valor vigilancia
            $table->decimal('valor_mantenimiento', 10, 2)->default(0); // Valor mantenimiento
            $table->decimal('otros_conceptos', 10, 2)->default(0); // Otros conceptos
            $table->decimal('total_recibo', 10, 2); // Total del recibo
            $table->text('observaciones')->nullable(); // Observaciones del recibo
            $table->enum('estado', ['activo', 'vencido', 'anulado'])->default('activo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recibo_gasto_comuns');
    }
};
