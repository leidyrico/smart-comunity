<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ReciboGastoComun;
use Carbon\Carbon;

echo "=== PRUEBA DE FORMATO DE FECHAS EN VISTA ===\n\n";

// Obtener recibos con fechas problemáticas
$recibosProblematicos = ReciboGastoComun::whereIn('numero_recibo', ['REC-0003', 'REC-0015'])->get();

echo "Recibos con fechas problemáticas encontrados: {$recibosProblematicos->count()}\n\n";

foreach ($recibosProblematicos as $recibo) {
    echo "=== RECIBO: {$recibo->numero_recibo} ===\n";
    echo "Fecha Emisión (raw): {$recibo->fecha_emision}\n";
    echo "Fecha Emisión (Carbon): {$recibo->fecha_emision->toDateString()}\n";
    echo "Fecha Emisión (d/m/Y): {$recibo->fecha_emision->format('d/m/Y')}\n";
    echo "Fecha Emisión (Y-m-d): {$recibo->fecha_emision->format('Y-m-d')}\n";
    echo "Fecha Emisión (timestamp): {$recibo->fecha_emision->timestamp}\n";
    
    echo "\nFecha Vencimiento (raw): {$recibo->fecha_vencimiento}\n";
    echo "Fecha Vencimiento (Carbon): {$recibo->fecha_vencimiento->toDateString()}\n";
    echo "Fecha Vencimiento (d/m/Y): {$recibo->fecha_vencimiento->format('d/m/Y')}\n";
    echo "Fecha Vencimiento (Y-m-d): {$recibo->fecha_vencimiento->format('Y-m-d')}\n";
    echo "Fecha Vencimiento (timestamp): {$recibo->fecha_vencimiento->timestamp}\n";
    
    // Verificar si está vencido
    echo "\n¿Está vencido?: " . ($recibo->estaVencido() ? 'SÍ' : 'NO') . "\n";
    echo "Fecha actual: " . now()->format('Y-m-d H:i:s') . "\n";
    
    echo "\n" . str_repeat('-', 50) . "\n\n";
}

// Verificar todos los recibos para detectar patrones
echo "=== ANÁLISIS GENERAL DE FECHAS ===\n\n";

$todosRecibos = ReciboGastoComun::orderBy('fecha_emision')->get();

echo "Total de recibos: {$todosRecibos->count()}\n\n";

$fechasRaras = [];
$formatosProblematicos = [];

foreach ($todosRecibos as $recibo) {
    // Verificar si la fecha de emisión es válida
    try {
        $fechaEmision = $recibo->fecha_emision;
        $fechaVencimiento = $recibo->fecha_vencimiento;
        
        // Verificar fechas que podrían ser problemáticas
        if ($fechaEmision->day == 29 && $fechaEmision->month == 2) {
            $fechasRaras[] = "Recibo {$recibo->numero_recibo}: 29 de febrero en año {$fechaEmision->year}";
        }
        
        if ($fechaEmision->day == 28 && $fechaEmision->month == 2) {
            $fechasRaras[] = "Recibo {$recibo->numero_recibo}: 28 de febrero en año {$fechaEmision->year}";
        }
        
        // Verificar si la fecha de vencimiento es anterior a la de emisión
        if ($fechaVencimiento->lt($fechaEmision)) {
            $formatosProblematicos[] = "Recibo {$recibo->numero_recibo}: Vencimiento ({$fechaVencimiento->format('d/m/Y')}) anterior a emisión ({$fechaEmision->format('d/m/Y')})";
        }
        
    } catch (\Exception $e) {
        $formatosProblematicos[] = "Recibo {$recibo->numero_recibo}: Error al procesar fechas - {$e->getMessage()}";
    }
}

if (!empty($fechasRaras)) {
    echo "Fechas especiales encontradas:\n";
    foreach ($fechasRaras as $fecha) {
        echo "  - {$fecha}\n";
    }
    echo "\n";
}

if (!empty($formatosProblematicos)) {
    echo "Problemas de formato encontrados:\n";
    foreach ($formatosProblematicos as $problema) {
        echo "  - {$problema}\n";
    }
    echo "\n";
}

if (empty($fechasRaras) && empty($formatosProblematicos)) {
    echo "No se encontraron problemas evidentes con las fechas.\n";
    echo "Las fechas parecen estar correctamente formateadas y almacenadas.\n\n";
}

echo "=== FIN DEL ANÁLISIS ===\n";