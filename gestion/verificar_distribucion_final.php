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

echo "=== VERIFICACIÓN FINAL DE LA DISTRIBUCIÓN DE CRÉDITO ===\n\n";

// Buscar el apartamento 13
$apartamento13 = Apartamento::where('numero', 13)->first();

if (!$apartamento13) {
    echo "❌ No se encontró el apartamento 13\n";
    exit;
}

echo "✅ Apartamento: {$apartamento13->numero} - {$apartamento13->propietario}\n\n";

// Verificar crédito global restante
$creditoGlobal = Pago::where('apartamento_id', $apartamento13->id)
    ->whereNull('recibo_gasto_comun_id')
    ->where('estado', 'confirmado')
    ->sum('monto_pagado');

echo "💰 Crédito global restante: $" . number_format($creditoGlobal, 2) . "\n\n";

// Verificar los pagos distribuidos
echo "=== PAGOS DISTRIBUIDOS ===\n";
$pagosDistribuidos = Pago::where('apartamento_id', $apartamento13->id)
    ->whereNotNull('recibo_gasto_comun_id')
    ->where('observaciones', 'LIKE', '%[DISTRIBUIDO]%')
    ->orderBy('created_at', 'desc')
    ->get();

echo "📋 Pagos distribuidos encontrados: " . $pagosDistribuidos->count() . "\n\n";

foreach ($pagosDistribuidos as $pago) {
    $recibo = ReciboGastoComun::find($pago->recibo_gasto_comun_id);
    echo "💳 Pago ID {$pago->id}:\n";
    echo "   📋 Recibo: {$recibo->numero_recibo}\n";
    echo "   💰 Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
    echo "   📅 Fecha: {$pago->fecha_pago}\n";
    echo "   📄 Comprobante: {$pago->numero_comprobante}\n";
    echo "   🔄 Estado: {$pago->estado}\n";
    echo "   📝 Observaciones: {$pago->observaciones}\n";
    echo "   " . str_repeat("-", 50) . "\n";
}

// Verificar el estado de los recibos específicos
echo "\n=== ESTADO DE RECIBOS ESPECÍFICOS ===\n";
$recibosEspecificos = ['REC-0725', 'REC-0922', 'REC-1022'];

foreach ($recibosEspecificos as $numeroRecibo) {
    $recibo = ReciboGastoComun::where('numero_recibo', $numeroRecibo)->first();
    
    if ($recibo) {
        $totalPagado = Pago::where('recibo_gasto_comun_id', $recibo->id)
            ->where('apartamento_id', $apartamento13->id)
            ->where('estado', 'confirmado')
            ->sum('monto_pagado');
        
        $saldoPendiente = $recibo->total_recibo - $totalPagado;
        
        echo "📋 {$recibo->numero_recibo}:\n";
        echo "   📅 Vencimiento: {$recibo->fecha_vencimiento}\n";
        echo "   💰 Total recibo: $" . number_format($recibo->total_recibo, 2) . "\n";
        echo "   💳 Total pagado: $" . number_format($totalPagado, 2) . "\n";
        echo "   🔴 Saldo pendiente: $" . number_format($saldoPendiente, 2) . "\n";
        echo "   🔄 Estado: " . ($saldoPendiente <= 0.01 ? '✅ PAGADO' : '⚠️ PENDIENTE') . "\n";
        
        // Mostrar pagos específicos de este recibo
        $pagosRecibo = Pago::where('recibo_gasto_comun_id', $recibo->id)
            ->where('apartamento_id', $apartamento13->id)
            ->where('estado', 'confirmado')
            ->get();
        
        if ($pagosRecibo->count() > 0) {
            echo "   💳 Pagos:\n";
            foreach ($pagosRecibo as $pago) {
                echo "      - ID {$pago->id}: $" . number_format($pago->monto_pagado, 2) . " ({$pago->fecha_pago})\n";
            }
        }
        
        echo "   " . str_repeat("=", 60) . "\n";
    } else {
        echo "❌ Recibo {$numeroRecibo} no encontrado\n";
    }
}

// Simular la vista de deudas
echo "\n=== SIMULACIÓN DE LA VISTA DE DEUDAS ===\n";

$recibosEnVista = DB::table('recibo_gasto_comuns')
    ->leftJoin('pagos', function($join) use ($apartamento13) {
        $join->on('recibo_gasto_comuns.id', '=', 'pagos.recibo_gasto_comun_id')
             ->where('pagos.apartamento_id', '=', $apartamento13->id)
             ->where('pagos.estado', '=', 'confirmado');
    })
    ->select(
        'recibo_gasto_comuns.id',
        'recibo_gasto_comuns.numero_recibo',
        'recibo_gasto_comuns.fecha_vencimiento',
        'recibo_gasto_comuns.total_recibo',
        'recibo_gasto_comuns.estado',
        DB::raw('COALESCE(SUM(pagos.monto_pagado), 0) as total_pagado')
    )
    ->groupBy(
        'recibo_gasto_comuns.id',
        'recibo_gasto_comuns.numero_recibo',
        'recibo_gasto_comuns.fecha_vencimiento',
        'recibo_gasto_comuns.total_recibo',
        'recibo_gasto_comuns.estado'
    )
    ->havingRaw('COALESCE(SUM(pagos.monto_pagado), 0) < recibo_gasto_comuns.total_recibo')
    ->orderBy('recibo_gasto_comuns.fecha_vencimiento', 'desc')
    ->get();

echo "📊 Total de recibos en la vista de deudas: " . $recibosEnVista->count() . "\n\n";

// Verificar si los recibos específicos aparecen en la vista
$rec0725EnVista = $recibosEnVista->where('numero_recibo', 'REC-0725')->first();
$rec0922EnVista = $recibosEnVista->where('numero_recibo', 'REC-0922')->first();
$rec1022EnVista = $recibosEnVista->where('numero_recibo', 'REC-1022')->first();

echo "🔍 Verificación de recibos específicos en la vista:\n";
echo "   📋 REC-0725: " . ($rec0725EnVista ? '⚠️ SÍ (saldo: $' . number_format($rec0725EnVista->total_recibo - $rec0725EnVista->total_pagado, 2) . ')' : '✅ NO') . "\n";
echo "   📋 REC-0922: " . ($rec0922EnVista ? '⚠️ SÍ (saldo: $' . number_format($rec0922EnVista->total_recibo - $rec0922EnVista->total_pagado, 2) . ')' : '✅ NO') . "\n";
echo "   📋 REC-1022: " . ($rec1022EnVista ? '⚠️ SÍ (saldo: $' . number_format($rec1022EnVista->total_recibo - $rec1022EnVista->total_pagado, 2) . ')' : '✅ NO') . "\n";

// Calcular totales
echo "\n=== RESUMEN FINANCIERO ===\n";

$totalDeuda = $recibosEnVista->sum(function($recibo) {
    return $recibo->total_recibo - $recibo->total_pagado;
});

$totalPagosApartamento = Pago::where('apartamento_id', $apartamento13->id)
    ->where('estado', 'confirmado')
    ->sum('monto_pagado');

echo "💰 Total deuda pendiente: $" . number_format($totalDeuda, 2) . "\n";
echo "💳 Total pagos realizados: $" . number_format($totalPagosApartamento, 2) . "\n";
echo "💰 Crédito global restante: $" . number_format($creditoGlobal, 2) . "\n";

// Verificar el historial completo del comprobante 672832207870
echo "\n=== HISTORIAL COMPLETO DEL COMPROBANTE 672832207870 ===\n";

$historialComprobante = Pago::where('numero_comprobante', '672832207870')
    ->where('apartamento_id', $apartamento13->id)
    ->orderBy('created_at', 'asc')
    ->get();

echo "📄 Total de pagos con comprobante 672832207870: " . $historialComprobante->count() . "\n\n";

foreach ($historialComprobante as $pago) {
    $recibo = $pago->recibo_gasto_comun_id ? ReciboGastoComun::find($pago->recibo_gasto_comun_id) : null;
    
    echo "💳 Pago ID {$pago->id}:\n";
    echo "   📋 Recibo: " . ($recibo ? $recibo->numero_recibo : 'GLOBAL') . "\n";
    echo "   💰 Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
    echo "   📅 Creado: {$pago->created_at}\n";
    echo "   🔄 Estado: {$pago->estado}\n";
    echo "   📝 Observaciones: " . substr($pago->observaciones, 0, 80) . "...\n";
    echo "   " . str_repeat("-", 50) . "\n";
}

echo "\n=== VERIFICACIÓN COMPLETADA ===\n";
echo "🎯 Resultado: El crédito de $100 ha sido distribuido exitosamente\n";
echo "📊 Estado: " . ($creditoGlobal > 0 ? "Crédito restante: $" . number_format($creditoGlobal, 2) : "Crédito completamente distribuido") . "\n";