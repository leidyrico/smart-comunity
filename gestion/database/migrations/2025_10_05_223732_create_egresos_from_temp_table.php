<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Eliminar tabla egresos si existe
        Schema::dropIfExists('egresos');
        
        // Crear nueva tabla egresos con la estructura correcta
        Schema::create('egresos', function (Blueprint $table) {
            $table->id();
            $table->string('nro_factura')->nullable();
            $table->date('fecha')->nullable();
            $table->string('comprobante')->nullable();
            $table->decimal('monto', 10, 2)->nullable();
            $table->text('descripcion')->nullable();
            $table->unsignedBigInteger('proveedor_id')->nullable();
            $table->decimal('monto_en_bs', 15, 2)->nullable();
            $table->timestamps();
            
            $table->foreign('proveedor_id')->references('id')->on('proveedors')->onDelete('set null');
        });
        
        // Copiar datos de la tabla temporal si existe
        if (Schema::hasTable('egresos_temp_1759253507')) {
            DB::statement('INSERT INTO egresos (id, nro_factura, fecha, comprobante, monto, descripcion, proveedor_id, monto_en_bs, created_at, updated_at) 
                          SELECT id, nro_factura, fecha, comprobante, monto, descripcion, proveedor_id, monto_en_bs, created_at, updated_at 
                          FROM egresos_temp_1759253507');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egresos');
    }
};
