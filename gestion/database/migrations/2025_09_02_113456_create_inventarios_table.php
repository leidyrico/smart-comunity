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
        Schema::create('inventarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('categoria')->nullable();
            $table->integer('cantidad')->default(0);
            $table->decimal('precio_unitario', 10, 2)->nullable();
            $table->string('ubicacion')->nullable();
            $table->string('estado')->default('disponible'); // disponible, en_uso, mantenimiento, dañado
            $table->date('fecha_adquisicion')->nullable();
            $table->string('proveedor')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventarios');
    }
};
