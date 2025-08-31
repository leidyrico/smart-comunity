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

echo "=== REDISTRIBUCIÓN DEL PAGO DE $100 - APARTAMENTO 13 ===\n\n";

// Buscar apartamento 13
$apartamento = Apartamento::where('numero', 13)->first();
if (!$apartamento) {
    echo "❌ No se encontró el apartamento 13\n";
    exit;
}

echo "✅ Apartamento encontrado: {$apartamento->numero} - {$apartamento->propietario}\n\n";

// Buscar los pagos rechazados con comprobante 672832207870
$pagosRechazados = Pago::where('numero_comprobante', '672832207870')
    ->where('apartamento_id', $apartamento->id)
    ->where('estado', 'rechazado')
    ->get();

if ($pagosRechazados->isEmpty()) {
    echo "❌ No se encontraron pagos rechazados con el comprobante 672832207870\n";
    exit;
}

echo "📋 Pagos rechazados encontrados: " . $pagosRechazados->count() . "\n";
$montoTotalRechazado = $pagosRechazados->sum('monto_pagado');
echo "💰 Monto total rechazado: $" . number_format($montoTotalRechazado, 2) . "\n\n";

foreach ($pagosRechazados as $pago) {
    echo "   📋 Pago ID {$pago->id}: $" . number_format($pago->monto_pagado, 2) . "\n";
    if ($pago->recibo_gasto_comun_id) {
        $recibo = ReciboGastoComun::find($pago->recibo_gasto_comun_id);
        if ($recibo) {
            echo "      📄 Recibo: {$recibo->numero_recibo}\n";
        }
    }
}

// Buscar recibos pendientes del apartamento 13 (sin pagos confirmados)
echo "\n=== BUSCANDO RECIBOS PENDIENTES DEL APARTAMENTO 13 ===\n";

// Buscar recibos que tienen pagos pendientes o sin pagos para este apartamento
$recibosPendientes = ReciboGastoComun::whereHas('pagos', function($query) use ($apartamento) {
        $query->where('apartamento_id', $apartamento->id)
              ->where('estado', '!=', 'confirmado');
    })
    ->orWhereDoesntHave('pagos', function($query) use ($apartamento) {
        $query->where('apartamento_id', $apartamento->id);
    })
    ->where('estado', '!=', 'anulado')
    ->orderBy('fecha_vencimiento', 'asc')
    ->get();

// Filtrar solo los que realmente corresponden al apartamento 13
$recibosPendientesFiltrados = collect();
foreach ($recibosPendientes as $recibo) {
    // Verificar si este recibo tiene pagos confirmados del apartamento 13
    $pagoConfirmado = Pago::where('apartamento_id', $apartamento->id)
        ->where('recibo_gasto_comun_id', $recibo->id)
        ->where('estado', 'confirmado')
        ->exists();
    
    if (!$pagoConfirmado && $recibo->total_recibo > 0) {
        $recibosPendientesFiltrados->push($recibo);
    }
}

$recibosPendientes = $recibosPendientesFiltrados;

if ($recibosPendientes->isEmpty()) {
    echo "❌ No se encontraron recibos pendientes para el apartamento 13\n";
    exit;
}

echo "✅ Recibos pendientes encontrados: " . $recibosPendientes->count() . "\n";
$montoTotalPendiente = $recibosPendientes->sum('total_recibo');
echo "💰 Monto total pendiente: $" . number_format($montoTotalPendiente, 2) . "\n\n";

foreach ($recibosPendientes as $index => $recibo) {
    echo "   " . ($index + 1) . ". {$recibo->numero_recibo} - $" . number_format($recibo->total_recibo, 2) . " (Vence: {$recibo->fecha_vencimiento})\n";
}

// Confirmar redistribución
echo "\n=== PLAN DE REDISTRIBUCIÓN ===\n";
echo "💰 Monto a redistribuir: $" . number_format($montoTotalRechazado, 2) . "\n";
echo "📋 Recibos disponibles: " . $recibosPendientes->count() . "\n";

if ($montoTotalRechazado > $montoTotalPendiente) {
    echo "⚠️  ADVERTENCIA: El monto a redistribuir ($" . number_format($montoTotalRechazado, 2) . ") es mayor que el total pendiente ($" . number_format($montoTotalPendiente, 2) . ")\n";
    echo "💡 Se aplicará el monto disponible y el resto quedará como crédito\n";
}

echo "\n¿Desea continuar con la redistribución? (y/n): ";
$confirmacion = trim(fgets(STDIN));

if (strtolower($confirmacion) !== 'y' && strtolower($confirmacion) !== 'yes' && strtolower($confirmacion) !== 'si' && strtolower($confirmacion) !== 's') {
    echo "❌ Redistribución cancelada\n";
    exit;
}

echo "\n=== INICIANDO REDISTRIBUCIÓN ===\n";

// Crear backup antes de la redistribución
echo "📋 Creando backup de pagos...\n";
$backupData = [];
foreach ($pagosRechazados as $pago) {
    $backupData[] = [
        'id' => $pago->id,
        'apartamento_id' => $pago->apartamento_id,
        'recibo_gasto_comun_id' => $pago->recibo_gasto_comun_id,
        'monto_pagado' => $pago->monto_pagado,
        'fecha_pago' => $pago->fecha_pago,
        'metodo_pago' => $pago->metodo_pago,
        'numero_comprobante' => $pago->numero_comprobante,
        'observaciones' => $pago->observaciones,
        'estado' => $pago->estado,
        'created_at' => $pago->created_at,
        'updated_at' => $pago->updated_at
    ];
}

$backupFile = 'backup_redistribucion_pago_100_' . date('Y_m_d_H_i_s') . '.json';
file_put_contents($backupFile, json_encode($backupData, JSON_PRETTY_PRINT));
echo "✅ Backup creado: {$backupFile}\n\n";

DB::beginTransaction();

try {
    $montoRestante = $montoTotalRechazado;
    $pagosCreados = 0;
    
    // Eliminar los pagos rechazados
    echo "🗑️  Eliminando pagos rechazados...\n";
    foreach ($pagosRechazados as $pago) {
        echo "   ❌ Eliminando pago ID {$pago->id} ($" . number_format($pago->monto_pagado, 2) . ")\n";
        $pago->delete();
    }
    
    // Redistribuir el monto entre los recibos pendientes
    echo "\n💰 Redistribuyendo monto...\n";
    
    foreach ($recibosPendientes as $recibo) {
        if ($montoRestante <= 0) {
            break;
        }
        
        $montoAplicar = min($montoRestante, $recibo->total_recibo);
        
        // Crear nuevo pago
        $nuevoPago = new Pago();
        $nuevoPago->apartamento_id = $apartamento->id;
        $nuevoPago->recibo_gasto_comun_id = $recibo->id;
        $nuevoPago->monto_pagado = $montoAplicar;
        $nuevoPago->fecha_pago = now()->format('Y-m-d');
        $nuevoPago->metodo_pago = 'transferencia';
        $nuevoPago->numero_comprobante = '672832207870';
        $nuevoPago->observaciones = '[REDISTRIBUIDO] Pago global de $100 redistribuido correctamente al apartamento 13';
        $nuevoPago->estado = 'confirmado';
        $nuevoPago->save();
        
        echo "   ✅ Creado pago ID {$nuevoPago->id}: $" . number_format($montoAplicar, 2) . " para {$recibo->numero_recibo}\n";
        
        $montoRestante -= $montoAplicar;
        $pagosCreados++;
    }
    
    // Si queda monto restante, crear un pago global
    if ($montoRestante > 0) {
        $pagoGlobal = new Pago();
        $pagoGlobal->apartamento_id = $apartamento->id;
        $pagoGlobal->recibo_gasto_comun_id = null; // Pago global
        $pagoGlobal->monto_pagado = $montoRestante;
        $pagoGlobal->fecha_pago = now()->format('Y-m-d');
        $pagoGlobal->metodo_pago = 'transferencia';
        $pagoGlobal->numero_comprobante = '672832207870';
        $pagoGlobal->observaciones = '[REDISTRIBUIDO] Crédito restante del pago global de $100 - Apartamento 13';
        $pagoGlobal->estado = 'confirmado';
        $pagoGlobal->save();
        
        echo "   💳 Creado crédito global ID {$pagoGlobal->id}: $" . number_format($montoRestante, 2) . "\n";
        $pagosCreados++;
    }
    
    DB::commit();
    
    echo "\n=== REDISTRIBUCIÓN COMPLETADA ===\n";
    echo "✅ Pagos eliminados: " . $pagosRechazados->count() . "\n";
    echo "✅ Pagos creados: {$pagosCreados}\n";
    echo "💰 Monto redistribuido: $" . number_format($montoTotalRechazado, 2) . "\n";
    echo "📋 Backup guardado en: {$backupFile}\n";
    
    // Verificar el estado final
    echo "\n=== VERIFICACIÓN FINAL ===\n";
    $pagosFinales = Pago::where('numero_comprobante', '672832207870')
        ->where('apartamento_id', $apartamento->id)
        ->where('estado', 'confirmado')
        ->get();
    
    echo "✅ Pagos activos con comprobante 672832207870: " . $pagosFinales->count() . "\n";
    echo "💰 Monto total activo: $" . number_format($pagosFinales->sum('monto_pagado'), 2) . "\n";
    
    foreach ($pagosFinales as $pago) {
        if ($pago->recibo_gasto_comun_id) {
            $recibo = ReciboGastoComun::find($pago->recibo_gasto_comun_id);
            echo "   📋 $" . number_format($pago->monto_pagado, 2) . " → {$recibo->numero_recibo}\n";
        } else {
            echo "   💳 $" . number_format($pago->monto_pagado, 2) . " → Crédito global\n";
        }
    }
    
} catch (Exception $e) {
    DB::rollback();
    echo "❌ Error durante la redistribución: " . $e->getMessage() . "\n";
    echo "💡 La transacción fue revertida. Los datos originales se mantienen intactos.\n";
    exit;
}

echo "\n🎯 REDISTRIBUCIÓN EXITOSA: El pago de $100 ahora está correctamente aplicado al apartamento 13\n";