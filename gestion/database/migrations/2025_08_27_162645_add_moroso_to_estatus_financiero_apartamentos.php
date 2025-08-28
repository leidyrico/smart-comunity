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
        // Modificar el enum para incluir 'moroso'
        DB::statement("ALTER TABLE apartamentos MODIFY COLUMN estatus_financiero ENUM('solvente', 'deudor', 'moroso') DEFAULT 'solvente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir el enum a su estado original
        DB::statement("ALTER TABLE apartamentos MODIFY COLUMN estatus_financiero ENUM('solvente', 'deudor') DEFAULT 'solvente'");
    }
};
