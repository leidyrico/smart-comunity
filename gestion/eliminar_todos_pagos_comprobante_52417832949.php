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

echo "=== ELIMINACIÓN DE PAGOS CON COMPROBANTE 52417832949 - APARTAMENTO 34 ===\n\n";

// Buscar el apartamento 34
$apartamento34 = Apartamento::where('numero', 34)->first();

if (!$apartamento34) {
    echo "❌ No se encontró el apartamento 34\n";
    exit;
}

echo "✅ Apartamento: {$apartamento34->numero} - {$apartamento34->propietario}\n\n";

// Buscar todos los pagos con comprobante 52417832949 del apartamento 34
$pagosAEliminar = Pago::where('apartamento_id', $apartamento34->id)
    ->where('numero_comprobante', '52417832949')
    ->get();

if ($pagosAEliminar->isEmpty()) {
    echo "❌ No se encontraron pagos con comprobante 52417832949 para el apartamento 34\n";
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

echo "=== INICIANDO ELIMINACIÓN AUTOMÁTICA ===\n";

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
            'apartamento_numero' => $apartamento34->numero,
            'apartamento_propietario' => $apartamento34->propietario,
            'recibo_gasto_comun_id' => $pago->recibo_gasto_comun_id,
            'recibo_numero' => $recibo ? $recibo->numero_recibo : 'GLOBAL',
            'monto_pagado' => $pago->monto_pagado,
            'fecha_pago' => $pago->fecha_pago,
            'numero_comprobante' => $pago->numero_comprobante,
            'estado' => $pago->estado,
            'observaciones' => $pago->observaciones,
            'created_at' => $pago->created_at,
            'updated_at' => $pago->updated_at,
            'eliminado_en' => now()
        ];
    }
    
    // Guardar backup
    $backupFile = 'backup_eliminacion_comprobante_52417832949_apt34_' . date('Y-m-d_H-i-s') . '.json';
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
$pagosRestantes = Pago::where('apartamento_id', $apartamento34->id)
    ->where('numero_comprobante', '52417832949')
    ->count();

echo "📊 Pagos restantes con comprobante 52417832949: {$pagosRestantes}\n";

if ($pagosRestantes == 0) {
    echo "✅ Todos los pagos con comprobante 52417832949 han sido eliminados\n";
} else {
    echo "⚠️  Advertencia: Aún quedan {$pagosRestantes} pagos con este comprobante\n";
}

// Verificar crédito global
$creditoGlobal = Pago::where('apartamento_id', $apartamento34->id)
    ->whereNull('recibo_gasto_comun_id')
    ->where('estado', 'confirmado')
    ->sum('monto_pagado');

echo "💰 Crédito global actual: $" . number_format($creditoGlobal, 2) . "\n";

// Verificar estado de los recibos afectados
echo "\n=== ESTADO DE RECIBOS AFECTADOS ===\n";
$recibosAfectados = ['REC-0725', 'REC-0525', 'REC-0625'];

foreach ($recibosAfectados as $numeroRecibo) {
    $recibo = ReciboGastoComun::where('numero_recibo', $numeroRecibo)->first();
    
    if ($recibo) {
        $totalPagado = Pago::where('recibo_gasto_comun_id', $recibo->id)
            ->where('apartamento_id', $apartamento34->id)
            ->where('estado', 'confirmado')
            ->sum('monto_pagado');
        
        echo "📋 {$recibo->numero_recibo}:\n";
        echo "   💰 Total recibo: $" . number_format($recibo->total_recibo, 2) . "\n";
        echo "   💳 Total pagado: $" . number_format($totalPagado, 2) . "\n";
        echo "   🔄 Estado: " . ($totalPagado > 0 ? 'CON PAGOS' : 'SIN PAGOS') . "\n";
        echo "\n";
    }
}

// Mostrar resumen final
echo "=== RESUMEN FINAL ===\n";
echo "✅ Pagos eliminados: " . count($backupData) . "\n";
echo "💰 Monto total eliminado: $" . number_format($montoTotalAEliminar, 2) . "\n";
echo "📄 Comprobante 52417832949: COMPLETAMENTE ELIMINADO\n";
echo "💳 Crédito global actual: $" . number_format($creditoGlobal, 2) . "\n";
echo "📁 Backup disponible: {$backupFile}\n";

echo "\n=== ELIMINACIÓN COMPLETADA ===\n";