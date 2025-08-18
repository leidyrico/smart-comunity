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
        Schema::create('actas', function (Blueprint $table) {
            $table->id();
            $table->string('nro_acta')->unique();
            $table->string('nombre_acta');
            $table->date('fecha');
            $table->text('descripcion');
            $table->longText('archivo_contenido')->nullable(); // Almacena el archivo en base64
            $table->string('archivo_nombre')->nullable();
            $table->string('archivo_tipo')->nullable(); // MIME type (application/pdf, image/jpeg, etc.)
            $table->unsignedInteger('archivo_tamaño')->nullable(); // Tamaño en bytes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actas');
    }
};
