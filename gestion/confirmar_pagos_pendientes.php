<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Apartamento;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

echo "=== CONFIRMACIÓN MASIVA DE PAGOS PENDIENTES ===\n\n";

// Obtener todos los pagos pendientes
$pagosPendientes = Pago::where('estado', 'pendiente_confirmacion')
    ->with(['apartamento', 'reciboGastoComun'])
    ->get();

echo "Total de pagos pendientes: {$pagosPendientes->count()}\n\n";

if ($pagosPendientes->count() == 0) {
    echo "No hay pagos pendientes de confirmación.\n";
    exit;
}

// Mostrar resumen por apartamento
echo "=== RESUMEN POR APARTAMENTO ===\n";
$apartamentosAfectados = [];

foreach ($pagosPendientes as $pago) {
    $apartamentoId = $pago->apartamento_id;
    
    if (!isset($apartamentosAfectados[$apartamentoId])) {
        $apartamentosAfectados[$apartamentoId] = [
            'apartamento' => $pago->apartamento,
            'pagos' => [],
            'total' => 0
        ];
    }
    
    $apartamentosAfectados[$apartamentoId]['pagos'][] = $pago;
    $apartamentosAfectados[$apartamentoId]['total'] += $pago->monto_pagado;
}

foreach ($apartamentosAfectados as $data) {
    $apartamento = $data['apartamento'];
    echo "Apartamento {$apartamento->numero}: {$data['total']} pagos, $" . number_format($data['total'], 2) . "\n";
}

echo "\n¿Desea confirmar TODOS los pagos pendientes? (y/n): ";
$handle = fopen("php://stdin", "r");
$confirmacion = trim(fgets($handle));
fclose($handle);

if (strtolower($confirmacion) !== 'y' && strtolower($confirmacion) !== 'yes') {
    echo "Operación cancelada.\n";
    exit;
}

echo "\n=== CONFIRMANDO PAGOS ===\n";

DB::beginTransaction();

try {
    $pagosConfirmados = 0;
    $apartamentosActualizados = [];
    
    foreach ($pagosPendientes as $pago) {
        // Confirmar el pago
        $pago->update(['estado' => 'confirmado']);
        $pagosConfirmados++;
        
        // Agregar apartamento a la lista de actualización
        $apartamentosActualizados[$pago->apartamento_id] = $pago->apartamento;
        
        echo "✅ Pago {$pago->id} confirmado (Apartamento {$pago->apartamento->numero}, $" . number_format($pago->monto_pagado, 2) . ")\n";
    }
    
    echo "\n=== ACTUALIZANDO ESTATUS FINANCIERO ===\n";
    
    foreach ($apartamentosActualizados as $apartamento) {
        $estatusAnterior = $apartamento->estatus_financiero;
        $saldoAnterior = $apartamento->saldo_pendiente;
        
        // Refrescar el apartamento para obtener el saldo actualizado
        $apartamento->refresh();
        
        // Actualizar estatus financiero
        $nuevoEstatus = $apartamento->actualizarEstatusFinanciero();
        
        // Refrescar nuevamente para obtener los datos actualizados
        $apartamento->refresh();
        
        echo "Apartamento {$apartamento->numero}:\n";
        echo "  Estatus: {$estatusAnterior} → {$apartamento->estatus_financiero}\n";
        echo "  Saldo: $" . number_format($saldoAnterior, 2) . " → $" . number_format($apartamento->saldo_pendiente, 2) . "\n";
        
        if ($estatusAnterior !== $apartamento->estatus_financiero) {
            echo "  🔄 ESTATUS ACTUALIZADO\n";
        } else {
            echo "  ✅ Estatus sin cambios\n";
        }
        echo "\n";
    }
    
    DB::commit();
    
    echo "=== RESUMEN FINAL ===\n";
    echo "✅ {$pagosConfirmados} pagos confirmados exitosamente\n";
    echo "✅ " . count($apartamentosActualizados) . " apartamentos procesados\n";
    echo "✅ Estatus financiero actualizado para todos los apartamentos afectados\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "❌ Error durante la confirmación: " . $e->getMessage() . "\n";
    echo "🔄 Todos los cambios han sido revertidos\n";
}

echo "\n=== FIN DE LA CONFIRMACIÓN ===\n";

?>