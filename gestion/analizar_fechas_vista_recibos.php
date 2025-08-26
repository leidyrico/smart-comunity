<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ReciboGastoComun;
use Carbon\Carbon;

echo "=== ANÁLISIS DE FECHAS EN VISTA DE RECIBOS ===\n\n";

// Obtener todos los recibos ordenados como en el controlador
$recibos = ReciboGastoComun::orderBy('periodo', 'desc')
    ->orderBy('fecha_emision', 'desc')
    ->get();

echo "Total de recibos encontrados: {$recibos->count()}\n\n";

if ($recibos->count() === 0) {
    echo "❌ No hay recibos para analizar\n";
    exit(1);
}

echo "=== ANÁLISIS DETALLADO DE FECHAS ===\n\n";

$problemasEncontrados = [];
$fechasAnalizadas = [];

foreach ($recibos as $index => $recibo) {
    $numero = $index + 1;
    echo "--- RECIBO #{$numero}: {$recibo->numero_recibo} ---\n";
    echo "Período: {$recibo->periodo}\n";
    
    // Analizar fecha de emisión
    echo "\nFecha de Emisión:\n";
    echo "  - Raw (DB): {$recibo->getRawOriginal('fecha_emision')}\n";
    echo "  - Carbon: {$recibo->fecha_emision}\n";
    echo "  - Formato d/m/Y: {$recibo->fecha_emision->format('d/m/Y')}\n";
    echo "  - Año: {$recibo->fecha_emision->year}\n";
    echo "  - Mes: {$recibo->fecha_emision->month}\n";
    echo "  - Día: {$recibo->fecha_emision->day}\n";
    
    // Analizar fecha de vencimiento
    echo "\nFecha de Vencimiento:\n";
    echo "  - Raw (DB): {$recibo->getRawOriginal('fecha_vencimiento')}\n";
    echo "  - Carbon: {$recibo->fecha_vencimiento}\n";
    echo "  - Formato d/m/Y: {$recibo->fecha_vencimiento->format('d/m/Y')}\n";
    echo "  - Año: {$recibo->fecha_vencimiento->year}\n";
    echo "  - Mes: {$recibo->fecha_vencimiento->month}\n";
    echo "  - Día: {$recibo->fecha_vencimiento->day}\n";
    
    // Detectar problemas potenciales
    $problemas = [];
    
    // Problema 1: Fechas fuera del rango esperado (2020-2030)
    if ($recibo->fecha_emision->year < 2020 || $recibo->fecha_emision->year > 2030) {
        $problemas[] = "Fecha de emisión fuera del rango 2020-2030: {$recibo->fecha_emision->year}";
    }
    
    if ($recibo->fecha_vencimiento->year < 2020 || $recibo->fecha_vencimiento->year > 2030) {
        $problemas[] = "Fecha de vencimiento fuera del rango 2020-2030: {$recibo->fecha_vencimiento->year}";
    }
    
    // Problema 2: Fecha de vencimiento anterior a fecha de emisión
    if ($recibo->fecha_vencimiento->lt($recibo->fecha_emision)) {
        $problemas[] = "Fecha de vencimiento anterior a fecha de emisión";
    }
    
    // Problema 3: Fechas especiales (29 de febrero, etc.)
    if ($recibo->fecha_emision->month == 2 && $recibo->fecha_emision->day == 29) {
        $problemas[] = "Fecha de emisión es 29 de febrero (año bisiesto)";
    }
    
    if ($recibo->fecha_vencimiento->month == 2 && $recibo->fecha_vencimiento->day == 29) {
        $problemas[] = "Fecha de vencimiento es 29 de febrero (año bisiesto)";
    }
    
    // Problema 4: Diferencias entre raw y formatted
    $rawEmision = $recibo->getRawOriginal('fecha_emision');
    $rawVencimiento = $recibo->getRawOriginal('fecha_vencimiento');
    
    try {
        $carbonFromRawEmision = Carbon::parse($rawEmision);
        if (!$carbonFromRawEmision->equalTo($recibo->fecha_emision)) {
            $problemas[] = "Discrepancia entre fecha raw y Carbon en emisión";
        }
    } catch (Exception $e) {
        $problemas[] = "Error al parsear fecha raw de emisión: {$e->getMessage()}";
    }
    
    try {
        $carbonFromRawVencimiento = Carbon::parse($rawVencimiento);
        if (!$carbonFromRawVencimiento->equalTo($recibo->fecha_vencimiento)) {
            $problemas[] = "Discrepancia entre fecha raw y Carbon en vencimiento";
        }
    } catch (Exception $e) {
        $problemas[] = "Error al parsear fecha raw de vencimiento: {$e->getMessage()}";
    }
    
    if (!empty($problemas)) {
        echo "\n🚨 PROBLEMAS DETECTADOS:\n";
        foreach ($problemas as $problema) {
            echo "  - {$problema}\n";
        }
        $problemasEncontrados[$recibo->numero_recibo] = $problemas;
    } else {
        echo "\n✅ Sin problemas detectados\n";
    }
    
    // Guardar fechas para análisis estadístico
    $fechasAnalizadas[] = [
        'recibo' => $recibo->numero_recibo,
        'emision_year' => $recibo->fecha_emision->year,
        'emision_month' => $recibo->fecha_emision->month,
        'emision_day' => $recibo->fecha_emision->day,
        'vencimiento_year' => $recibo->fecha_vencimiento->year,
        'vencimiento_month' => $recibo->fecha_vencimiento->month,
        'vencimiento_day' => $recibo->fecha_vencimiento->day,
    ];
    
    echo "\n" . str_repeat('-', 60) . "\n\n";
}

echo "=== RESUMEN DE PROBLEMAS ===\n\n";

if (empty($problemasEncontrados)) {
    echo "✅ No se encontraron problemas en las fechas\n";
} else {
    $cantidadProblemas = count($problemasEncontrados);
    echo "🚨 Se encontraron problemas en {$cantidadProblemas} recibos:\n\n";
    foreach ($problemasEncontrados as $numeroRecibo => $problemas) {
        echo "Recibo {$numeroRecibo}:\n";
        foreach ($problemas as $problema) {
            echo "  - {$problema}\n";
        }
        echo "\n";
    }
}

echo "=== ANÁLISIS ESTADÍSTICO ===\n\n";

// Análisis de años
$años = array_merge(
    array_column($fechasAnalizadas, 'emision_year'),
    array_column($fechasAnalizadas, 'vencimiento_year')
);
$añosUnicos = array_unique($años);
sort($añosUnicos);
echo "Años encontrados: " . implode(', ', $añosUnicos) . "\n";

// Análisis de meses
$meses = array_merge(
    array_column($fechasAnalizadas, 'emision_month'),
    array_column($fechasAnalizadas, 'vencimiento_month')
);
$mesesUnicos = array_unique($meses);
sort($mesesUnicos);
echo "Meses encontrados: " . implode(', ', $mesesUnicos) . "\n";

// Fechas especiales
$fechas29Feb = array_filter($fechasAnalizadas, function($fecha) {
    return ($fecha['emision_month'] == 2 && $fecha['emision_day'] == 29) ||
           ($fecha['vencimiento_month'] == 2 && $fecha['vencimiento_day'] == 29);
});

if (!empty($fechas29Feb)) {
    echo "\n🗓️ Recibos con 29 de febrero:\n";
    foreach ($fechas29Feb as $fecha) {
        echo "  - {$fecha['recibo']}\n";
    }
}

echo "\n=== SIMULACIÓN DE VISTA BLADE ===\n\n";

// Simular cómo se verían las fechas en la vista
echo "Simulando formato de vista (d/m/Y):\n\n";
foreach ($recibos->take(5) as $recibo) {
    echo "Recibo: {$recibo->numero_recibo}\n";
    echo "  Emisión: {$recibo->fecha_emision->format('d/m/Y')}\n";
    echo "  Vencimiento: {$recibo->fecha_vencimiento->format('d/m/Y')}\n";
    echo "  Período: {$recibo->periodo}\n";
    echo "  Total: $" . number_format($recibo->total, 2) . "\n";
    echo "\n";
}

echo "=== FIN DEL ANÁLISIS ===\n";