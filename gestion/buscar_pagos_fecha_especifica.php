<?php

require_once __DIR__ . '/vendor/autoload.php';

// Configurar la aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Simular una request HTTP
$request = Illuminate\Http\Request::create('/', 'GET');
$app->instance('request', $request);

use Illuminate\Support\Facades\DB;
use App\Models\Pago;
use App\Models\Apartamento;
use App\Models\ReciboGastoComun;

echo "=== BÚSQUEDA DE PAGOS CON FECHA 04/09/2025 ===\n\n";

try {
    // Buscar pagos con fecha específica usando consulta SQL directa
    $pagosFecha = DB::table('pagos')
        ->where('fecha_pago', '2025-09-04')
        ->get();
    
    echo "Pagos encontrados con fecha 2025-09-04: " . $pagosFecha->count() . "\n\n";
    
    if ($pagosFecha->count() > 0) {
        foreach ($pagosFecha->take(10) as $pago) {
            $apartamento = DB::table('apartamentos')->where('id', $pago->apartamento_id)->first();
            $recibo = $pago->recibo_gasto_comun_id ? 
                DB::table('recibo_gasto_comuns')->where('id', $pago->recibo_gasto_comun_id)->first() : null;
            
            echo "📋 Pago ID: {$pago->id}\n";
            echo "   🏠 Apartamento: " . ($apartamento ? $apartamento->numero : 'N/A') . "\n";
            echo "   📄 Recibo: " . ($recibo ? $recibo->numero_recibo : 'GLOBAL') . "\n";
            echo "   💰 Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
            echo "   📅 Fecha pago: {$pago->fecha_pago}\n";
            echo "   🔄 Estado: {$pago->estado}\n";
            echo "   📝 Observaciones: {$pago->observaciones}\n";
            echo "   📅 Creado: {$pago->created_at}\n";
            echo "   " . str_repeat("-", 50) . "\n";
        }
    }
    
    // Buscar también con formato de fecha diferente
    echo "\n=== BÚSQUEDA CON OTROS FORMATOS DE FECHA ===\n";
    
    $formatosFecha = [
        '2025-09-04 00:00:00',
        '04/09/2025',
        '2025-04-09', // Por si hay confusión de formato
    ];
    
    foreach ($formatosFecha as $formato) {
        $pagos = DB::table('pagos')
            ->where('fecha_pago', 'LIKE', '%' . $formato . '%')
            ->get();
        
        if ($pagos->count() > 0) {
            echo "Formato {$formato}: " . $pagos->count() . " pagos encontrados\n";
        }
    }
    
    // Buscar pagos con monto 0 y fecha no nula
    echo "\n=== PAGOS CON MONTO $0.00 Y FECHA NO NULA ===\n";
    
    $pagosCeroConFecha = DB::table('pagos')
        ->where('monto_pagado', 0)
        ->whereNotNull('fecha_pago')
        ->orderBy('fecha_pago', 'desc')
        ->get();
    
    echo "Pagos con monto $0.00 y fecha: " . $pagosCeroConFecha->count() . "\n\n";
    
    // Agrupar por fecha
    $fechasAgrupadas = $pagosCeroConFecha->groupBy('fecha_pago');
    
    echo "Fechas más comunes en pagos $0.00:\n";
    foreach ($fechasAgrupadas->sortByDesc(function($pagos) {
        return $pagos->count();
    })->take(10) as $fecha => $pagos) {
        echo "📅 {$fecha}: " . $pagos->count() . " pagos\n";
    }
    
    // Verificar si la fecha 04/09/2025 aparece en algún formato
    echo "\n=== VERIFICACIÓN ESPECÍFICA FECHA 04/09/2025 ===\n";
    
    $fechasBuscar = [
        '2025-09-04',
        '2025-04-09',
        '04/09/2025',
        '09/04/2025'
    ];
    
    $encontradoAlguno = false;
    
    foreach ($fechasBuscar as $fechaBuscar) {
        $count = DB::table('pagos')
            ->where('fecha_pago', $fechaBuscar)
            ->count();
        
        if ($count > 0) {
            echo "✅ Formato {$fechaBuscar}: {$count} pagos\n";
            $encontradoAlguno = true;
        }
    }
    
    if (!$encontradoAlguno) {
        echo "❌ No se encontraron pagos con fecha 04/09/2025 en ningún formato\n";
    }
    
    // Buscar en observaciones si hay alguna referencia a esta fecha
    echo "\n=== BÚSQUEDA EN OBSERVACIONES ===\n";
    
    $pagosConObservacion = DB::table('pagos')
        ->where('observaciones', 'LIKE', '%04/09/2025%')
        ->orWhere('observaciones', 'LIKE', '%2025-09-04%')
        ->orWhere('observaciones', 'LIKE', '%09/04/2025%')
        ->get();
    
    if ($pagosConObservacion->count() > 0) {
        echo "Pagos con fecha en observaciones: " . $pagosConObservacion->count() . "\n";
        foreach ($pagosConObservacion as $pago) {
            echo "- ID {$pago->id}: {$pago->observaciones}\n";
        }
    } else {
        echo "No se encontraron referencias a 04/09/2025 en observaciones\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== BÚSQUEDA COMPLETADA ===\n";