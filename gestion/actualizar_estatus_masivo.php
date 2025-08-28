<?php

require_once __DIR__ . '/vendor/autoload.php';

// Cargar configuración de Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;

echo "=== ACTUALIZACIÓN MASIVA DE ESTATUS FINANCIERO ===\n\n";

// Obtener todos los apartamentos
$apartamentos = Apartamento::all();
echo "Total de apartamentos a procesar: {$apartamentos->count()}\n\n";

$contadores = [
    'solvente' => 0,
    'deudor' => 0,
    'moroso' => 0,
    'sin_cambios' => 0
];

foreach ($apartamentos as $apartamento) {
    $estatusAnterior = $apartamento->estatus_financiero;
    
    // Actualizar estatus financiero usando el método del modelo
    $nuevoEstatus = $apartamento->actualizarEstatusFinanciero();
    
    if ($estatusAnterior !== $nuevoEstatus) {
        echo "Apartamento {$apartamento->numero}: {$estatusAnterior} → {$nuevoEstatus}\n";
        $contadores[$nuevoEstatus]++;
    } else {
        $contadores['sin_cambios']++;
    }
}

echo "\n=== RESUMEN DE ACTUALIZACIÓN ===\n";
echo "Apartamentos solventes: {$contadores['solvente']}\n";
echo "Apartamentos deudores: {$contadores['deudor']}\n";
echo "Apartamentos morosos: {$contadores['moroso']}\n";
echo "Sin cambios: {$contadores['sin_cambios']}\n";
echo "\nTotal procesados: " . array_sum($contadores) . "\n";

echo "\n=== VERIFICACIÓN FINAL ===\n";
$verificacion = Apartamento::selectRaw('estatus_financiero, COUNT(*) as total')
    ->groupBy('estatus_financiero')
    ->get();

foreach ($verificacion as $grupo) {
    echo "Estatus '{$grupo->estatus_financiero}': {$grupo->total} apartamentos\n";
}

echo "\n=== ACTUALIZACIÓN COMPLETADA ===\n";