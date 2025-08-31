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

echo "=== DISTRIBUCIÓN DE CRÉDITO GLOBAL DE $100 ===\n\n";

// Buscar el apartamento 13
$apartamento13 = Apartamento::where('numero', 13)->first();

if (!$apartamento13) {
    echo "❌ No se encontró el apartamento 13\n";
    exit;
}

echo "✅ Apartamento: {$apartamento13->numero} - {$apartamento13->propietario}\n\n";

// Verificar el crédito global disponible
$creditoGlobal = Pago::where('apartamento_id', $apartamento13->id)
    ->whereNull('recibo_gasto_comun_id')
    ->where('estado', 'confirmado')
    ->sum('monto_pagado');

echo "💰 Crédito global disponible: $" . number_format($creditoGlobal, 2) . "\n";

if ($creditoGlobal <= 0) {
    echo "❌ No hay crédito global disponible para distribuir\n";
    exit;
}

// Buscar recibos vencidos del apartamento 13 que no estén completamente pagados
echo "\n=== BUSCANDO RECIBOS VENCIDOS PENDIENTES ===\n";

$recibosVencidos = DB::table('recibo_gasto_comuns')
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
    ->where('recibo_gasto_comuns.estado', 'vencido')
    ->where('recibo_gasto_comuns.fecha_vencimiento', '<', now())
    ->groupBy(
        'recibo_gasto_comuns.id',
        'recibo_gasto_comuns.numero_recibo',
        'recibo_gasto_comuns.fecha_vencimiento',
        'recibo_gasto_comuns.total_recibo',
        'recibo_gasto_comuns.estado'
    )
    ->havingRaw('COALESCE(SUM(pagos.monto_pagado), 0) < recibo_gasto_comuns.total_recibo')
    ->orderBy('recibo_gasto_comuns.fecha_vencimiento', 'asc') // Más antiguos primero
    ->get();

if ($recibosVencidos->isEmpty()) {
    echo "❌ No se encontraron recibos vencidos pendientes\n";
    exit;
}

echo "📋 Recibos vencidos encontrados: " . $recibosVencidos->count() . "\n\n";

// Mostrar recibos encontrados
foreach ($recibosVencidos as $recibo) {
    $saldoPendiente = $recibo->total_recibo - $recibo->total_pagado;
    echo "📋 {$recibo->numero_recibo}:\n";
    echo "   📅 Vencimiento: {$recibo->fecha_vencimiento}\n";
    echo "   💰 Total: $" . number_format($recibo->total_recibo, 2) . "\n";
    echo "   💳 Pagado: $" . number_format($recibo->total_pagado, 2) . "\n";
    echo "   🔴 Pendiente: $" . number_format($saldoPendiente, 2) . "\n";
    echo "   " . str_repeat("-", 40) . "\n";
}

// Estrategia de distribución: priorizar el más actual y luego los más antiguos
echo "\n=== ESTRATEGIA DE DISTRIBUCIÓN ===\n";
echo "1. Identificar el recibo vencido más actual\n";
echo "2. Aplicar parte del crédito al recibo más actual\n";
echo "3. Distribuir el resto entre los recibos más antiguos\n\n";

// Encontrar el recibo más actual (última fecha de vencimiento)
$reciboMasActual = $recibosVencidos->sortByDesc('fecha_vencimiento')->first();
echo "📅 Recibo más actual: {$reciboMasActual->numero_recibo} (Vence: {$reciboMasActual->fecha_vencimiento})\n";

// Encontrar recibos más antiguos (excluyendo el más actual)
$recibosAntiguos = $recibosVencidos->where('id', '!=', $reciboMasActual->id)
    ->sortBy('fecha_vencimiento');
echo "📅 Recibos antiguos: " . $recibosAntiguos->count() . "\n\n";

// Calcular distribución
$montoDisponible = $creditoGlobal;
$distribucion = [];

// 1. Aplicar 50% al recibo más actual (o lo que necesite si es menos)
$saldoMasActual = $reciboMasActual->total_recibo - $reciboMasActual->total_pagado;
$montoParaActual = min($montoDisponible * 0.5, $saldoMasActual);

if ($montoParaActual > 0) {
    $distribucion[] = [
        'recibo' => $reciboMasActual,
        'monto' => $montoParaActual,
        'tipo' => 'actual'
    ];
    $montoDisponible -= $montoParaActual;
}

// 2. Distribuir el resto entre los recibos más antiguos
if ($montoDisponible > 0 && $recibosAntiguos->count() > 0) {
    foreach ($recibosAntiguos as $recibo) {
        if ($montoDisponible <= 0) break;
        
        $saldoPendiente = $recibo->total_recibo - $recibo->total_pagado;
        $montoAplicar = min($montoDisponible, $saldoPendiente);
        
        if ($montoAplicar > 0) {
            $distribucion[] = [
                'recibo' => $recibo,
                'monto' => $montoAplicar,
                'tipo' => 'antiguo'
            ];
            $montoDisponible -= $montoAplicar;
        }
    }
}

// Mostrar plan de distribución
echo "=== PLAN DE DISTRIBUCIÓN ===\n";
$totalDistribuido = 0;
foreach ($distribucion as $item) {
    $recibo = $item['recibo'];
    $monto = $item['monto'];
    $tipo = $item['tipo'];
    
    echo "📋 {$recibo->numero_recibo} ({$tipo}): $" . number_format($monto, 2) . "\n";
    $totalDistribuido += $monto;
}

echo "\n💰 Total a distribuir: $" . number_format($totalDistribuido, 2) . "\n";
echo "💰 Crédito restante: $" . number_format($creditoGlobal - $totalDistribuido, 2) . "\n\n";

if (empty($distribucion)) {
    echo "❌ No se pudo crear un plan de distribución\n";
    exit;
}

// Confirmar la operación
echo "⚠️  ¿Proceder con la distribución? (y/N): ";
$handle = fopen("php://stdin", "r");
$confirmacion = trim(fgets($handle));
fclose($handle);

if (strtolower($confirmacion) !== 'y') {
    echo "❌ Operación cancelada\n";
    exit;
}

echo "\n=== EJECUTANDO DISTRIBUCIÓN ===\n";

try {
    DB::beginTransaction();
    
    // Buscar el pago global para reducirlo
    $pagoGlobal = Pago::where('apartamento_id', $apartamento13->id)
        ->whereNull('recibo_gasto_comun_id')
        ->where('estado', 'confirmado')
        ->first();
    
    if (!$pagoGlobal) {
        throw new Exception("No se encontró el pago global");
    }
    
    echo "💳 Pago global original: $" . number_format($pagoGlobal->monto_pagado, 2) . "\n";
    
    // Crear backup
    $backupData = [
        'pago_global_original' => [
            'id' => $pagoGlobal->id,
            'monto_pagado' => $pagoGlobal->monto_pagado,
            'observaciones' => $pagoGlobal->observaciones
        ],
        'distribucion' => $distribucion
    ];
    
    $backupFile = 'backup_distribucion_credito_' . date('Y-m-d_H-i-s') . '.json';
    file_put_contents($backupFile, json_encode($backupData, JSON_PRETTY_PRINT));
    echo "✅ Backup guardado en: {$backupFile}\n\n";
    
    // Crear pagos específicos para cada recibo
    foreach ($distribucion as $item) {
        $recibo = $item['recibo'];
        $monto = $item['monto'];
        $tipo = $item['tipo'];
        
        $nuevoPago = new Pago();
        $nuevoPago->apartamento_id = $apartamento13->id;
        $nuevoPago->recibo_gasto_comun_id = $recibo->id;
        $nuevoPago->monto_pagado = $monto;
        $nuevoPago->fecha_pago = now()->format('Y-m-d');
        $nuevoPago->numero_comprobante = $pagoGlobal->numero_comprobante;
        $nuevoPago->estado = 'confirmado';
        $nuevoPago->observaciones = "[DISTRIBUIDO] Crédito global distribuido - Recibo {$tipo} - Monto: $" . number_format($monto, 2);
        $nuevoPago->save();
        
        echo "✅ Pago creado para {$recibo->numero_recibo}: $" . number_format($monto, 2) . " (ID: {$nuevoPago->id})\n";
    }
    
    // Actualizar o eliminar el pago global
    $nuevoMontoGlobal = $pagoGlobal->monto_pagado - $totalDistribuido;
    
    if ($nuevoMontoGlobal > 0.01) { // Mantener si queda más de 1 centavo
        $pagoGlobal->monto_pagado = $nuevoMontoGlobal;
        $pagoGlobal->observaciones = $pagoGlobal->observaciones . " [DISTRIBUIDO] $" . number_format($totalDistribuido, 2) . " distribuido en " . date('Y-m-d H:i:s');
        $pagoGlobal->save();
        echo "✅ Pago global actualizado: $" . number_format($nuevoMontoGlobal, 2) . "\n";
    } else {
        $pagoGlobal->delete();
        echo "✅ Pago global eliminado (distribuido completamente)\n";
    }
    
    DB::commit();
    
    echo "\n🎉 DISTRIBUCIÓN COMPLETADA EXITOSAMENTE\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "❌ Error durante la distribución: " . $e->getMessage() . "\n";
    echo "🔄 Transacción revertida\n";
    exit;
}

// Verificación final
echo "\n=== VERIFICACIÓN FINAL ===\n";

// Verificar crédito global restante
$creditoRestante = Pago::where('apartamento_id', $apartamento13->id)
    ->whereNull('recibo_gasto_comun_id')
    ->where('estado', 'confirmado')
    ->sum('monto_pagado');

echo "💰 Crédito global restante: $" . number_format($creditoRestante, 2) . "\n";

// Verificar pagos creados
$pagosCreados = Pago::where('apartamento_id', $apartamento13->id)
    ->whereNotNull('recibo_gasto_comun_id')
    ->where('observaciones', 'LIKE', '%[DISTRIBUIDO]%')
    ->get();

echo "📋 Pagos específicos creados: " . $pagosCreados->count() . "\n";

foreach ($pagosCreados as $pago) {
    $recibo = ReciboGastoComun::find($pago->recibo_gasto_comun_id);
    echo "   📋 {$recibo->numero_recibo}: $" . number_format($pago->monto_pagado, 2) . "\n";
}

echo "\n=== DISTRIBUCIÓN FINALIZADA ===\n";