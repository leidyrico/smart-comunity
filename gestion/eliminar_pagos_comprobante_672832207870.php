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

echo "=== ELIMINACIÓN DE PAGOS CON COMPROBANTE 672832207870 ===\n\n";

// Buscar el apartamento 13
$apartamento13 = Apartamento::where('numero', 13)->first();

if (!$apartamento13) {
    echo "❌ No se encontró el apartamento 13\n";
    exit;
}

echo "✅ Apartamento: {$apartamento13->numero} - {$apartamento13->propietario}\n\n";

// Buscar todos los pagos con comprobante 672832207870 del apartamento 13
$pagosAEliminar = Pago::where('apartamento_id', $apartamento13->id)
    ->where('numero_comprobante', '672832207870')
    ->get();

if ($pagosAEliminar->isEmpty()) {
    echo "❌ No se encontraron pagos con comprobante 672832207870 para el apartamento 13\n";
    exit;
}

echo "📋 Pagos encontrados para eliminar: " . $pagosAEliminar->count() . "\n\n";

$montoTotalAEliminar = 0;

foreach ($pagosAEliminar as $pago) {
    $recibo = $pago->recibo_gasto_comun_id ? ReciboGastoComun::find($pago->recibo_gasto_comun_id) : null;
    
    echo "💳 Pago ID {$pago->id}:\n";
    echo "   📋 Recibo: " . ($recibo ? $recibo->numero_recibo : 'GLOBAL') . "\n";
    echo "   💰 Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
    echo "   📅 Fecha: {$pago->fecha_pago}\n";
    echo "   🔄 Estado: {$pago->estado}\n";
    echo "   📝 Observaciones: " . substr($pago->observaciones, 0, 60) . "...\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $montoTotalAEliminar += $pago->monto_pagado;
}

echo "\n💰 Monto total a eliminar: $" . number_format($montoTotalAEliminar, 2) . "\n\n";

// Confirmar la operación
echo "⚠️  ADVERTENCIA: Esta operación eliminará TODOS los pagos con comprobante 672832207870\n";
echo "Esto incluye:\n";
echo "- Pagos específicos a recibos que no pertenecen al apartamento 13\n";
echo "- Cualquier pago global restante\n";
echo "- Total a eliminar: $" . number_format($montoTotalAEliminar, 2) . "\n\n";
echo "¿Continuar con la eliminación? (y/N): ";

$handle = fopen("php://stdin", "r");
$confirmacion = trim(fgets($handle));
fclose($handle);

if (strtolower($confirmacion) !== 'y') {
    echo "❌ Operación cancelada\n";
    exit;
}

echo "\n=== INICIANDO ELIMINACIÓN ===\n";

try {
    DB::beginTransaction();
    
    // Crear backup completo antes de eliminar
    echo "📋 Creando backup completo...\n";
    $backupData = [];
    
    foreach ($pagosAEliminar as $pago) {
        $recibo = $pago->recibo_gasto_comun_id ? ReciboGastoComun::find($pago->recibo_gasto_comun_id) : null;
        
        $backupData[] = [
            'id' => $pago->id,
            'apartamento_id' => $pago->apartamento_id,
            'recibo_gasto_comun_id' => $pago->recibo_gasto_comun_id,
            'recibo_numero' => $recibo ? $recibo->numero_recibo : 'GLOBAL',
            'monto_pagado' => $pago->monto_pagado,
            'fecha_pago' => $pago->fecha_pago,
            'numero_comprobante' => $pago->numero_comprobante,
            'estado' => $pago->estado,
            'observaciones' => $pago->observaciones,
            'created_at' => $pago->created_at,
            'updated_at' => $pago->updated_at
        ];
    }
    
    // Guardar backup
    $backupFile = 'backup_eliminacion_comprobante_672832207870_' . date('Y-m-d_H-i-s') . '.json';
    file_put_contents($backupFile, json_encode($backupData, JSON_PRETTY_PRINT));
    echo "✅ Backup guardado en: {$backupFile}\n\n";
    
    // Eliminar cada pago
    echo "🗑️  Eliminando pagos...\n";
    foreach ($pagosAEliminar as $pago) {
        $recibo = $pago->recibo_gasto_comun_id ? ReciboGastoComun::find($pago->recibo_gasto_comun_id) : null;
        $reciboInfo = $recibo ? $recibo->numero_recibo : 'GLOBAL';
        
        echo "   ❌ Eliminando pago ID {$pago->id} ({$reciboInfo}) - $" . number_format($pago->monto_pagado, 2) . "\n";
        $pago->delete();
    }
    
    DB::commit();
    
    echo "\n🎉 ELIMINACIÓN COMPLETADA EXITOSAMENTE\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "❌ Error durante la eliminación: " . $e->getMessage() . "\n";
    echo "🔄 Transacción revertida\n";
    exit;
}

// Verificación final
echo "\n=== VERIFICACIÓN FINAL ===\n";

// Verificar que no quedan pagos con el comprobante
$pagosRestantes = Pago::where('apartamento_id', $apartamento13->id)
    ->where('numero_comprobante', '672832207870')
    ->count();

echo "📊 Pagos restantes con comprobante 672832207870: {$pagosRestantes}\n";

if ($pagosRestantes == 0) {
    echo "✅ Todos los pagos con comprobante 672832207870 han sido eliminados\n";
} else {
    echo "⚠️  Advertencia: Aún quedan {$pagosRestantes} pagos con este comprobante\n";
}

// Verificar crédito global
$creditoGlobal = Pago::where('apartamento_id', $apartamento13->id)
    ->whereNull('recibo_gasto_comun_id')
    ->where('estado', 'confirmado')
    ->sum('monto_pagado');

echo "💰 Crédito global actual: $" . number_format($creditoGlobal, 2) . "\n";

// Verificar estado de los recibos afectados
echo "\n=== ESTADO DE RECIBOS AFECTADOS ===\n";
$recibosAfectados = ['REC-0922', 'REC-1022', 'REC-0725'];

foreach ($recibosAfectados as $numeroRecibo) {
    $recibo = ReciboGastoComun::where('numero_recibo', $numeroRecibo)->first();
    
    if ($recibo) {
        $totalPagado = Pago::where('recibo_gasto_comun_id', $recibo->id)
            ->where('apartamento_id', $apartamento13->id)
            ->where('estado', 'confirmado')
            ->sum('monto_pagado');
        
        echo "📋 {$recibo->numero_recibo}:\n";
        echo "   💰 Total recibo: $" . number_format($recibo->total_recibo, 2) . "\n";
        echo "   💳 Total pagado: $" . number_format($totalPagado, 2) . "\n";
        echo "   🔄 Estado: " . ($totalPagado > 0 ? 'CON PAGOS' : 'SIN PAGOS') . "\n";
        echo "\n";
    }
}

// Simular vista de deudas
echo "=== SIMULACIÓN DE VISTA DE DEUDAS ===\n";

$recibosEnVista = DB::table('recibo_gasto_comuns')
    ->leftJoin('pagos', function($join) use ($apartamento13) {
        $join->on('recibo_gasto_comuns.id', '=', 'pagos.recibo_gasto_comun_id')
             ->where('pagos.apartamento_id', '=', $apartamento13->id)
             ->where('pagos.estado', '=', 'confirmado');
    })
    ->select(
        'recibo_gasto_comuns.numero_recibo',
        DB::raw('COALESCE(SUM(pagos.monto_pagado), 0) as total_pagado'),
        'recibo_gasto_comuns.total_recibo'
    )
    ->groupBy('recibo_gasto_comuns.id', 'recibo_gasto_comuns.numero_recibo', 'recibo_gasto_comuns.total_recibo')
    ->havingRaw('COALESCE(SUM(pagos.monto_pagado), 0) < recibo_gasto_comuns.total_recibo')
    ->get();

echo "📊 Total de recibos en vista de deudas: " . $recibosEnVista->count() . "\n";

// Verificar recibos específicos
$rec0922EnVista = $recibosEnVista->where('numero_recibo', 'REC-0922')->first();
$rec1022EnVista = $recibosEnVista->where('numero_recibo', 'REC-1022')->first();
$rec0725EnVista = $recibosEnVista->where('numero_recibo', 'REC-0725')->first();

echo "\n🔍 Verificación de recibos específicos:\n";
echo "   📋 REC-0922: " . ($rec0922EnVista ? '⚠️ SÍ aparece en vista' : '✅ NO aparece en vista') . "\n";
echo "   📋 REC-1022: " . ($rec1022EnVista ? '⚠️ SÍ aparece en vista' : '✅ NO aparece en vista') . "\n";
echo "   📋 REC-0725: " . ($rec0725EnVista ? '⚠️ SÍ aparece en vista' : '✅ NO aparece en vista') . "\n";

echo "\n=== RESUMEN FINAL ===\n";
echo "✅ Pagos eliminados: " . count($backupData) . "\n";
echo "💰 Monto total eliminado: $" . number_format($montoTotalAEliminar, 2) . "\n";
echo "📄 Comprobante 672832207870: COMPLETAMENTE ELIMINADO\n";
echo "💳 Crédito global actual: $" . number_format($creditoGlobal, 2) . "\n";
echo "📊 Recibos en vista: " . $recibosEnVista->count() . "\n";

echo "\n=== ELIMINACIÓN COMPLETADA ===\n";