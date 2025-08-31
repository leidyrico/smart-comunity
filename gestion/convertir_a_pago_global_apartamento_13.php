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

echo "=== CONVERSIÓN A PAGO GLOBAL PARA APARTAMENTO 13 ===\n\n";

// Buscar el apartamento 13
$apartamento13 = Apartamento::where('numero', 13)->first();

if (!$apartamento13) {
    echo "❌ No se encontró el apartamento 13\n";
    exit;
}

echo "✅ Apartamento: {$apartamento13->numero} - {$apartamento13->propietario}\n\n";

// Buscar los pagos problemáticos con comprobante 672832207870
$pagosProblematicos = Pago::where('numero_comprobante', '672832207870')
    ->where('apartamento_id', $apartamento13->id)
    ->where('estado', 'confirmado')
    ->get();

if ($pagosProblematicos->isEmpty()) {
    echo "❌ No se encontraron pagos problemáticos para convertir\n";
    exit;
}

echo "📋 Pagos encontrados para conversión: " . $pagosProblematicos->count() . "\n";

$montoTotal = 0;
$recibosAfectados = [];

foreach ($pagosProblematicos as $pago) {
    $recibo = $pago->recibo_gasto_comun_id ? ReciboGastoComun::find($pago->recibo_gasto_comun_id) : null;
    echo "💰 Pago ID {$pago->id}: $" . number_format($pago->monto_pagado, 2);
    if ($recibo) {
        echo " → {$recibo->numero_recibo}";
        $recibosAfectados[] = $recibo->numero_recibo;
    }
    echo "\n";
    $montoTotal += $pago->monto_pagado;
}

echo "\n💰 Monto total a convertir: $" . number_format($montoTotal, 2) . "\n";
echo "📋 Recibos afectados: " . implode(', ', $recibosAfectados) . "\n\n";

// Confirmar la operación
echo "⚠️  ADVERTENCIA: Esta operación:\n";
echo "1. Eliminará los pagos específicos a REC-0922 y REC-1022\n";
echo "2. Creará un pago global de $" . number_format($montoTotal, 2) . " para el apartamento 13\n";
echo "3. Los recibos REC-0922 y REC-1022 volverán a aparecer como pendientes\n";
echo "\n¿Continuar? (y/N): ";

$handle = fopen("php://stdin", "r");
$confirmacion = trim(fgets($handle));
fclose($handle);

if (strtolower($confirmacion) !== 'y') {
    echo "❌ Operación cancelada\n";
    exit;
}

echo "\n=== INICIANDO CONVERSIÓN ===\n";

try {
    DB::beginTransaction();
    
    // 1. Crear backup de los pagos actuales
    echo "📋 Creando backup de pagos actuales...\n";
    $backupData = [];
    
    foreach ($pagosProblematicos as $pago) {
        $backupData[] = [
            'id' => $pago->id,
            'apartamento_id' => $pago->apartamento_id,
            'recibo_gasto_comun_id' => $pago->recibo_gasto_comun_id,
            'monto_pagado' => $pago->monto_pagado,
            'fecha_pago' => $pago->fecha_pago,
            'numero_comprobante' => $pago->numero_comprobante,
            'estado' => $pago->estado,
            'observaciones' => $pago->observaciones,
            'created_at' => $pago->created_at,
            'updated_at' => $pago->updated_at
        ];
    }
    
    // Guardar backup en archivo
    $backupFile = 'backup_pagos_conversion_global_' . date('Y-m-d_H-i-s') . '.json';
    file_put_contents($backupFile, json_encode($backupData, JSON_PRETTY_PRINT));
    echo "✅ Backup guardado en: {$backupFile}\n";
    
    // 2. Eliminar los pagos específicos
    echo "🗑️  Eliminando pagos específicos...\n";
    foreach ($pagosProblematicos as $pago) {
        echo "   ❌ Eliminando pago ID {$pago->id} ($" . number_format($pago->monto_pagado, 2) . ")\n";
        $pago->delete();
    }
    
    // 3. Crear el pago global
    echo "💳 Creando pago global...\n";
    $pagoGlobal = new Pago();
    $pagoGlobal->apartamento_id = $apartamento13->id;
    $pagoGlobal->recibo_gasto_comun_id = null; // NULL = pago global
    $pagoGlobal->monto_pagado = $montoTotal;
    $pagoGlobal->fecha_pago = now()->format('Y-m-d');
    $pagoGlobal->numero_comprobante = '672832207870';
    $pagoGlobal->estado = 'confirmado';
    $pagoGlobal->observaciones = '[CONVERTIDO A GLOBAL] Pago de $' . number_format($montoTotal, 2) . ' convertido de pagos específicos (' . implode(', ', $recibosAfectados) . ') a pago global para apartamento 13';
    $pagoGlobal->save();
    
    echo "✅ Pago global creado con ID: {$pagoGlobal->id}\n";
    
    DB::commit();
    
    echo "\n=== CONVERSIÓN COMPLETADA EXITOSAMENTE ===\n";
    
    // 4. Verificar el resultado
    echo "\n=== VERIFICACIÓN DEL RESULTADO ===\n";
    
    // Verificar el pago global
    $pagoGlobalVerificacion = Pago::where('apartamento_id', $apartamento13->id)
        ->whereNull('recibo_gasto_comun_id')
        ->where('numero_comprobante', '672832207870')
        ->first();
    
    if ($pagoGlobalVerificacion) {
        echo "✅ Pago global verificado:\n";
        echo "   💰 Monto: $" . number_format($pagoGlobalVerificacion->monto_pagado, 2) . "\n";
        echo "   📄 Comprobante: {$pagoGlobalVerificacion->numero_comprobante}\n";
        echo "   🔄 Estado: {$pagoGlobalVerificacion->estado}\n";
        echo "   📅 Fecha: {$pagoGlobalVerificacion->fecha_pago}\n";
    } else {
        echo "❌ Error: No se pudo verificar el pago global\n";
    }
    
    // Verificar que no hay pagos específicos
    $pagosEspecificosRestantes = Pago::where('numero_comprobante', '672832207870')
        ->where('apartamento_id', $apartamento13->id)
        ->whereNotNull('recibo_gasto_comun_id')
        ->count();
    
    echo "\n📊 Pagos específicos restantes con comprobante 672832207870: {$pagosEspecificosRestantes}\n";
    
    if ($pagosEspecificosRestantes == 0) {
        echo "✅ Todos los pagos específicos fueron eliminados correctamente\n";
    } else {
        echo "⚠️  Advertencia: Aún hay pagos específicos restantes\n";
    }
    
    // Verificar el estado de los recibos
    echo "\n=== ESTADO DE LOS RECIBOS AFECTADOS ===\n";
    $recibosVerificacion = ReciboGastoComun::whereIn('numero_recibo', $recibosAfectados)->get();
    
    foreach ($recibosVerificacion as $recibo) {
        $pagosRecibo = Pago::where('recibo_gasto_comun_id', $recibo->id)
            ->where('estado', 'confirmado')
            ->sum('monto_pagado');
        
        echo "📋 {$recibo->numero_recibo}:\n";
        echo "   💰 Monto recibo: $" . number_format($recibo->total_recibo, 2) . "\n";
        echo "   💳 Pagos confirmados: $" . number_format($pagosRecibo, 2) . "\n";
        echo "   🔄 Estado: " . ($pagosRecibo >= $recibo->total_recibo ? 'PAGADO' : 'PENDIENTE') . "\n";
        echo "\n";
    }
    
} catch (Exception $e) {
    DB::rollback();
    echo "❌ Error durante la conversión: " . $e->getMessage() . "\n";
    echo "🔄 Transacción revertida\n";
    exit;
}

echo "=== RESUMEN FINAL ===\n";
echo "✅ Pago de $" . number_format($montoTotal, 2) . " convertido a pago global\n";
echo "📋 Recibos {$recibosAfectados[0]} y {$recibosAfectados[1]} ahora aparecerán como pendientes\n";
echo "💳 El apartamento 13 tiene un crédito global de $" . number_format($montoTotal, 2) . "\n";
echo "📄 Comprobante: 672832207870\n";
echo "\n=== OPERACIÓN COMPLETADA ===\n";