<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;

echo "Iniciando actualización de estatus financieros...\n";

$apartamentos = Apartamento::all();
echo "Total apartamentos encontrados: " . $apartamentos->count() . "\n";

foreach ($apartamentos as $apartamento) {
    $estatusAnterior = $apartamento->estatus_financiero;
    $apartamento->actualizarEstatusFinanciero();
    $estatusNuevo = $apartamento->fresh()->estatus_financiero;
    
    echo "Apartamento {$apartamento->numero}: {$estatusAnterior} -> {$estatusNuevo}\n";
}

echo "\nActualización completada.\n";

// Mostrar resumen
$solventes = Apartamento::where('estatus_financiero', 'solvente')->count();
$deudores = Apartamento::where('estatus_financiero', 'deudor')->count();
$morosos = Apartamento::where('estatus_financiero', 'moroso')->count();

echo "\nResumen final:\n";
echo "Solventes: {$solventes}\n";
echo "Deudores: {$deudores}\n";
echo "Morosos: {$morosos}\n";