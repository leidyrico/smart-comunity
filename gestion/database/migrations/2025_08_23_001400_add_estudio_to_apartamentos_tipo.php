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
        // Modificar el enum para incluir 'estudio'
        DB::statement("ALTER TABLE apartamentos MODIFY COLUMN tipo ENUM('apartamento', 'local', 'parqueadero', 'deposito', 'estudio') DEFAULT 'apartamento'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir el enum a su estado original
        DB::statement("ALTER TABLE apartamentos MODIFY COLUMN tipo ENUM('apartamento', 'local', 'parqueadero', 'deposito') DEFAULT 'apartamento'");
    }
};