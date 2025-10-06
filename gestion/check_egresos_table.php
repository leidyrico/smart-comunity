<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    echo "Verificando tabla egresos...\n";
    
    if (Schema::hasTable('egresos')) {
        echo "✅ La tabla egresos EXISTE\n";
        $count = DB::table('egresos')->count();
        echo "Registros en la tabla: $count\n";
    } else {
        echo "❌ La tabla egresos NO EXISTE\n";
    }
    
    echo "\nTablas disponibles:\n";
    $tables = DB::select('SHOW TABLES');
    foreach ($tables as $table) {
        $tableName = array_values((array)$table)[0];
        echo "- $tableName\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}