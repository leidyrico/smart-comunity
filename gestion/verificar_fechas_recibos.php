<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ReciboGastoComun;
use Carbon\Carbon;

echo "=== VERIFICACIÓN DE FECHAS EN RECIBOS ===\n\n";

// Obtener los últimos 15 recibos
$recibos = ReciboGastoComun::orderBy('fecha_emision', 'desc')->take(15)->get();

echo "Total de recibos en la base de datos: " . ReciboGastoComun::count() . "\n";
echo "Mostrando los últimos 15 recibos:\n\n";

foreach ($recibos as $recibo) {
    echo "ID: {$recibo->id}\n";
    echo "Número: {$recibo->numero_recibo}\n";
    echo "Período: {$recibo->periodo}\n";
    echo "Fecha Emisión (raw): {$recibo->fecha_emision}\n";
    echo "Fecha Emisión (formato): {$recibo->fecha_emision->format('d/m/Y')}\n";
    echo "Fecha Vencimiento (raw): {$recibo->fecha_vencimiento}\n";
    echo "Fecha Vencimiento (formato): {$recibo->fecha_vencimiento->format('d/m/Y')}\n";
    echo "Estado: {$recibo->estado}\n";
    echo "Total: {$recibo->total_recibo}\n";
    echo "---\n";
}

// Verificar fechas problemáticas específicas
echo "\n=== VERIFICACIÓN DE FECHAS PROBLEMÁTICAS ===\n\n";

$fechasProblematicas = [
    '2024-02-29', // Año bisiesto válido
    '2025-02-28', // Febrero no bisiesto
    '2025-03-01'  // Posible conversión de 29/02/2025
];

foreach ($fechasProblematicas as $fecha) {
    $recibosConFecha = ReciboGastoComun::whereDate('fecha_emision', $fecha)
        ->orWhereDate('fecha_vencimiento', $fecha)
        ->get();
    
    echo "Recibos con fecha {$fecha}: {$recibosConFecha->count()}\n";
    
    foreach ($recibosConFecha as $recibo) {
        echo "  - {$recibo->numero_recibo}: Emisión {$recibo->fecha_emision->format('d/m/Y')}, Vencimiento {$recibo->fecha_vencimiento->format('d/m/Y')}\n";
    }
}

// Verificar si hay fechas con años fuera del rango esperado
echo "\n=== VERIFICACIÓN DE FECHAS FUERA DE RANGO ===\n\n";

$recibosFueraRango = ReciboGastoComun::where(function($query) {
    $query->whereYear('fecha_emision', '<', 2020)
          ->orWhereYear('fecha_emision', '>', 2030)
          ->orWhereYear('fecha_vencimiento', '<', 2020)
          ->orWhereYear('fecha_vencimiento', '>', 2030);
})->get();

echo "Recibos con fechas fuera del rango 2020-2030: {$recibosFueraRango->count()}\n";

foreach ($recibosFueraRango as $recibo) {
    echo "  - {$recibo->numero_recibo}: Emisión {$recibo->fecha_emision->format('d/m/Y')} (año {$recibo->fecha_emision->year}), Vencimiento {$recibo->fecha_vencimiento->format('d/m/Y')} (año {$recibo->fecha_vencimiento->year})\n";
}

echo "\n=== FIN DE LA VERIFICACIÓN ===\n";