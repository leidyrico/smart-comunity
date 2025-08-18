<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ESTRUCTURA TABLA USERS ===\n\n";

try {
    $columns = DB::select('DESCRIBE users');
    echo "Columnas en la tabla users:\n";
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type})\n";
    }
    
    echo "\n=== DATOS EN TABLA USERS ===\n";
    $users = DB::select('SELECT * FROM users');
    echo "Total usuarios: " . count($users) . "\n\n";
    
    foreach ($users as $user) {
        echo "Usuario ID: {$user->id}\n";
        foreach ($user as $field => $value) {
            echo "  {$field}: {$value}\n";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>