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
            $table->decimal('monto_en_bs', 15, 2)->nullable()->after('monto');
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
            $table->dropColumn('monto_en_bs');
        });
    }
};
