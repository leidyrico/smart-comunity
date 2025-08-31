<?php

require_once 'vendor/autoload.php';

// Configurar la aplicación Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\Pago;
use App\Models\ReciboGastoComun;
use Illuminate\Support\Facades\DB;

echo "=== INVESTIGACIÓN: ¿A QUÉ APARTAMENTO PERTENECEN REC-0922 Y REC-1022? ===\n\n";

// Buscar los recibos problemáticos
$recibosProblematicos = ReciboGastoComun::whereIn('numero_recibo', ['REC-0922', 'REC-1022'])
    ->orderBy('numero_recibo')
    ->get();

if ($recibosProblematicos->isEmpty()) {
    echo "❌ No se encontraron los recibos REC-0922 y REC-1022\n";
    exit;
}

echo "✅ Recibos encontrados: " . $recibosProblematicos->count() . "\n\n";

foreach ($recibosProblematicos as $recibo) {
    echo "📋 {$recibo->numero_recibo} (ID: {$recibo->id})\n";
    echo "   📅 Fecha vencimiento: {$recibo->fecha_vencimiento}\n";
    echo "   💰 Monto: $" . number_format($recibo->total_recibo, 2) . "\n";
    echo "   🔄 Estado: {$recibo->estado}\n";
    
    // Buscar TODOS los pagos históricos de este recibo
    echo "\n   === HISTORIAL DE PAGOS ===\n";
    $todosPagos = Pago::where('recibo_gasto_comun_id', $recibo->id)
        ->orderBy('created_at', 'asc')
        ->get();
    
    if ($todosPagos->isEmpty()) {
        echo "   ❌ No hay pagos registrados para este recibo\n";
    } else {
        echo "   📊 Total de pagos: " . $todosPagos->count() . "\n";
        
        foreach ($todosPagos as $pago) {
            $apartamento = Apartamento::find($pago->apartamento_id);
            echo "   📋 Pago ID {$pago->id}:\n";
            echo "      🏠 Apartamento: {$apartamento->numero} ({$apartamento->propietario})\n";
            echo "      💰 Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
            echo "      📅 Fecha: {$pago->fecha_pago}\n";
            echo "      📅 Creado: {$pago->created_at}\n";
            echo "      🔄 Estado: {$pago->estado}\n";
            echo "      📄 Comprobante: {$pago->numero_comprobante}\n";
            echo "      📝 Observaciones: {$pago->observaciones}\n";
            echo "      " . str_repeat("-", 40) . "\n";
        }
    }
    
    echo "   " . str_repeat("=", 70) . "\n\n";
}

// Buscar recibos similares por fecha para determinar el patrón
echo "=== ANÁLISIS DE PATRONES POR FECHA ===\n";

// Buscar recibos de septiembre y octubre 2022
echo "📅 Buscando recibos de septiembre-noviembre 2022...\n";
$recibosSimilares = ReciboGastoComun::whereBetween('fecha_vencimiento', ['2022-09-01', '2022-12-31'])
    ->orderBy('fecha_vencimiento')
    ->get();

echo "✅ Recibos encontrados en el período: " . $recibosSimilares->count() . "\n\n";

foreach ($recibosSimilares as $recibo) {
    echo "📋 {$recibo->numero_recibo} - Vence: {$recibo->fecha_vencimiento} - $" . number_format($recibo->total_recibo, 2) . "\n";
    
    // Verificar si tiene pagos
    $pagosRecibo = Pago::where('recibo_gasto_comun_id', $recibo->id)
        ->where('estado', '!=', 'rechazado')
        ->get();
    
    if ($pagosRecibo->count() > 0) {
        foreach ($pagosRecibo as $pago) {
            $apartamento = Apartamento::find($pago->apartamento_id);
            echo "   🏠 Apartamento {$apartamento->numero} - $" . number_format($pago->monto_pagado, 2) . " ({$pago->estado})\n";
        }
    } else {
        echo "   ❌ Sin pagos activos\n";
    }
    echo "\n";
}

// Buscar el apartamento que más pagos tiene en ese período
echo "=== ANÁLISIS DE APARTAMENTOS CON MÁS ACTIVIDAD EN EL PERÍODO ===\n";

$apartamentosActividad = DB::table('pagos')
    ->join('recibo_gasto_comuns', 'pagos.recibo_gasto_comun_id', '=', 'recibo_gasto_comuns.id')
    ->join('apartamentos', 'pagos.apartamento_id', '=', 'apartamentos.id')
    ->whereBetween('recibo_gasto_comuns.fecha_vencimiento', ['2022-09-01', '2022-12-31'])
    ->where('pagos.estado', '!=', 'rechazado')
    ->select(
        'apartamentos.id',
        'apartamentos.numero',
        'apartamentos.propietario',
        DB::raw('COUNT(*) as total_pagos'),
        DB::raw('SUM(pagos.monto_pagado) as total_monto')
    )
    ->groupBy('apartamentos.id', 'apartamentos.numero', 'apartamentos.propietario')
    ->orderBy('total_pagos', 'desc')
    ->get();

echo "📊 Apartamentos con más actividad en sep-nov 2022:\n";
foreach ($apartamentosActividad as $actividad) {
    echo "🏠 Apartamento {$actividad->numero} ({$actividad->propietario}): {$actividad->total_pagos} pagos - $" . number_format($actividad->total_monto, 2) . "\n";
}

// Buscar el comprobante 672832207870 en otros contextos
echo "\n=== ANÁLISIS DEL COMPROBANTE 672832207870 ===\n";

$pagosComprobante = Pago::where('numero_comprobante', '672832207870')
    ->orderBy('created_at', 'asc')
    ->get();

echo "📄 Total de pagos con comprobante 672832207870: " . $pagosComprobante->count() . "\n";

if ($pagosComprobante->count() > 0) {
    echo "📋 Historial completo del comprobante:\n";
    
    foreach ($pagosComprobante as $pago) {
        $apartamento = Apartamento::find($pago->apartamento_id);
        $recibo = $pago->recibo_gasto_comun_id ? ReciboGastoComun::find($pago->recibo_gasto_comun_id) : null;
        
        echo "   📋 Pago ID {$pago->id}:\n";
        echo "      🏠 Apartamento: {$apartamento->numero} ({$apartamento->propietario})\n";
        echo "      💰 Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
        echo "      📋 Recibo: " . ($recibo ? $recibo->numero_recibo : 'GLOBAL') . "\n";
        echo "      📅 Creado: {$pago->created_at}\n";
        echo "      🔄 Estado: {$pago->estado}\n";
        echo "      📝 Observaciones: {$pago->observaciones}\n";
        echo "      " . str_repeat("-", 40) . "\n";
    }
}

// Buscar pagos globales del apartamento 13
echo "\n=== PAGOS GLOBALES DEL APARTAMENTO 13 ===\n";

$apartamento13 = Apartamento::where('numero', 13)->first();
$pagosGlobales = Pago::where('apartamento_id', $apartamento13->id)
    ->whereNull('recibo_gasto_comun_id')
    ->orderBy('created_at', 'desc')
    ->get();

echo "💳 Pagos globales del apartamento 13: " . $pagosGlobales->count() . "\n";

if ($pagosGlobales->count() > 0) {
    foreach ($pagosGlobales as $pago) {
        echo "   📋 Pago ID {$pago->id}: $" . number_format($pago->monto_pagado, 2) . " - {$pago->estado}\n";
        echo "      📄 Comprobante: {$pago->numero_comprobante}\n";
        echo "      📅 Fecha: {$pago->fecha_pago}\n";
        echo "      📝 Observaciones: {$pago->observaciones}\n";
        echo "      " . str_repeat("-", 40) . "\n";
    }
}

echo "\n=== RECOMENDACIONES ===\n";
echo "💡 Basado en el análisis:\n";
echo "1. Verificar si REC-0922 y REC-1022 fueron creados específicamente para el apartamento 13\n";
echo "2. O si fueron asignados incorrectamente desde otro apartamento\n";
echo "3. Considerar crear un pago global de $100 para el apartamento 13 en lugar de asignarlo a recibos específicos\n";
echo "4. Revisar las observaciones del pago original para entender la intención\n";

echo "\n=== INVESTIGACIÓN COMPLETADA ===\n";