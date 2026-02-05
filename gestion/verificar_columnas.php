<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ESTRUCTURA DE LA TABLA APARTAMENTOS ===\n\n";

$columnas = DB::select('DESCRIBE apartamentos');

echo "Columnas encontradas:\n";
foreach ($columnas as $columna) {
    echo "- {$columna->Field} ({$columna->Type})\n";
}

echo "\n=== VERIFICACIÓN DE EMAILS ===\n\n";

$apartamentos = DB::table('apartamentos')
    ->whereNotNull('email')
    ->where('email', '!=', '')
    ->limit(5)
    ->get();

echo "Apartamentos con emails:\n";
foreach ($apartamentos as $apto) {
    echo "- ID: {$apto->id}, Email: {$apto->email}\n";
}

echo "\nTotal de apartamentos con email: " . $apartamentos->count() . "\n";