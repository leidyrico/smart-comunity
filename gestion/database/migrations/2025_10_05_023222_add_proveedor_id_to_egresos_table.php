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
        if (! Schema::hasTable('egresos_temp_1759253507')) {
            return;
        }

        Schema::table('egresos_temp_1759253507', function (Blueprint $table) {
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedors')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('egresos_temp_1759253507')) {
            return;
        }

        Schema::table('egresos_temp_1759253507', function (Blueprint $table) {
            $table->dropForeign(['proveedor_id']);
            $table->dropColumn('proveedor_id');
        });
    }
};
