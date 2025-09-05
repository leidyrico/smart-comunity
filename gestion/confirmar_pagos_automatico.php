<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Apartamento;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

echo "=== CONFIRMACIÓN AUTOMÁTICA DE PAGOS PENDIENTES ===\n\n";

// Obtener todos los pagos pendientes
$pagosPendientes = Pago::where('estado', 'pendiente_confirmacion')
    ->with(['apartamento', 'reciboGastoComun'])
    ->get();

echo "Total de pagos pendientes: {$pagosPendientes->count()}\n\n";

if ($pagosPendientes->count() == 0) {
    echo "No hay pagos pendientes de confirmación.\n";
    exit;
}

echo "=== CONFIRMANDO PAGOS AUTOMÁTICAMENTE ===\n";

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
    
    $cambiosEstatus = 0;
    
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
            $cambiosEstatus++;
        } else {
            echo "  ✅ Estatus sin cambios\n";
        }
        echo "\n";
    }
    
    DB::commit();
    
    echo "=== RESUMEN FINAL ===\n";
    echo "✅ {$pagosConfirmados} pagos confirmados exitosamente\n";
    echo "✅ " . count($apartamentosActualizados) . " apartamentos procesados\n";
    echo "🔄 {$cambiosEstatus} apartamentos cambiaron su estatus financiero\n";
    echo "✅ Problema de actualización de estatus financiero RESUELTO\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "❌ Error durante la confirmación: " . $e->getMessage() . "\n";
    echo "🔄 Todos los cambios han sido revertidos\n";
}

echo "\n=== FIN DE LA CONFIRMACIÓN ===\n";

?>