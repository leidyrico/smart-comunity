<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inquilinos', function (Blueprint $table) {
            $table->string('nombre_inquilino');
            $table->string('nro_apartamento');
            $table->decimal('monto_deuda', 10, 2);
            $table->date('fecha_deuda');
            $table->decimal('monto_ultimo_pago', 10, 2)->nullable();
            $table->date('fecha_ultimo_pago')->nullable();
            
            // Índice único para evitar duplicados por apartamento
            $table->unique('nro_apartamento');
        });
    }

    public function down(): void
    {
        Schema::table('inquilinos', function (Blueprint $table) {
            $table->dropUnique(['nro_apartamento']);
            $table->dropColumn([
                'nombre_inquilino',
                'nro_apartamento',
                'monto_deuda',
                'fecha_deuda',
                'monto_ultimo_pago',
                'fecha_ultimo_pago'
            ]);
        });
    }
};