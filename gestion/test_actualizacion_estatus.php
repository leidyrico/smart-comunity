<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Apartamento;
use App\Models\Pago;
use App\Models\ReciboGastoComun;
use Illuminate\Support\Facades\DB;

echo "=== TEST ACTUALIZACIÓN ESTATUS FINANCIERO ===\n\n";

// Buscar un apartamento deudor o moroso con saldo pendiente
$apartamento = Apartamento::whereIn('estatus_financiero', ['deudor', 'moroso'])
    ->get()
    ->filter(function($apt) {
        return $apt->saldo_pendiente > 0;
    })
    ->first();

if (!$apartamento) {
    echo "No se encontró un apartamento deudor/moroso con saldo pendiente.\n";
    exit;
}

echo "=== ESTADO INICIAL ===\n";
echo "Apartamento: {$apartamento->numero}\n";
echo "Propietario: {$apartamento->propietario}\n";
echo "Estatus financiero: {$apartamento->estatus_financiero}\n";
echo "Saldo pendiente: $" . number_format($apartamento->saldo_pendiente, 2) . "\n\n";

// Obtener todos los recibos asignados y encontrar uno con saldo pendiente
$recibosAsignados = ReciboGastoComun::whereHas('pagos', function($query) use ($apartamento) {
    $query->where('apartamento_id', $apartamento->id)
          ->where('estado', '!=', 'rechazado');
})
->whereIn('estado', ['activo', 'vencido'])
->get();

$reciboPendiente = null;
$saldoRecibo = 0;

foreach ($recibosAsignados as $recibo) {
    $totalPagadoRecibo = $apartamento->pagos()
        ->where('recibo_gasto_comun_id', $recibo->id)
        ->where('estado', 'confirmado')
        ->sum('monto_pagado');
    
    $saldoTemporal = $recibo->total_recibo - $totalPagadoRecibo;
    
    if ($saldoTemporal > 0) {
        $reciboPendiente = $recibo;
        $saldoRecibo = $saldoTemporal;
        break;
    }
}

if (!$reciboPendiente || $saldoRecibo <= 0) {
    echo "No se encontró un recibo con saldo pendiente para este apartamento.\n";
    echo "Detalles de recibos asignados:\n";
    foreach ($recibosAsignados as $recibo) {
        $totalPagado = $apartamento->pagos()
            ->where('recibo_gasto_comun_id', $recibo->id)
            ->where('estado', 'confirmado')
            ->sum('monto_pagado');
        $saldo = $recibo->total_recibo - $totalPagado;
        echo "- Recibo {$recibo->id}: Total $" . number_format($recibo->total_recibo, 2) . ", Pagado $" . number_format($totalPagado, 2) . ", Saldo $" . number_format($saldo, 2) . "\n";
    }
    exit;
}

echo "=== RECIBO SELECCIONADO ===\n";
echo "Recibo ID: {$reciboPendiente->id}\n";
echo "Total recibo: $" . number_format($reciboPendiente->total_recibo, 2) . "\n";
echo "Ya pagado: $" . number_format($reciboPendiente->total_recibo - $saldoRecibo, 2) . "\n";
echo "Saldo pendiente del recibo: $" . number_format($saldoRecibo, 2) . "\n\n";

// Simular el registro de un pago que cubra COMPLETAMENTE este recibo
echo "=== SIMULANDO PAGO COMPLETO ===\n";
echo "Registrando pago de $" . number_format($saldoRecibo, 2) . " para cubrir el recibo...\n";

DB::beginTransaction();

try {
    // Crear el pago
    $pago = Pago::create([
        'apartamento_id' => $apartamento->id,
        'recibo_gasto_comun_id' => $reciboPendiente->id,
        'monto_pagado' => $saldoRecibo,
        'fecha_pago' => now(),
        'metodo_pago' => 'transferencia',
        'numero_comprobante' => 'TEST-' . time(),
        'estado' => 'confirmado',
        'observaciones' => 'Pago de prueba para verificar actualización de estatus'
    ]);
    
    echo "✅ Pago creado con ID: {$pago->id}\n";
    
    // Verificar estado ANTES de actualizar estatus
    $apartamento->refresh();
    echo "\n=== DESPUÉS DEL PAGO (ANTES DE ACTUALIZAR ESTATUS) ===\n";
    echo "Saldo pendiente: $" . number_format($apartamento->saldo_pendiente, 2) . "\n";
    echo "Estatus financiero: {$apartamento->estatus_financiero}\n";
    
    // Actualizar estatus financiero (esto debería hacerse automáticamente en el controlador)
    echo "\nActualizando estatus financiero...\n";
    $nuevoEstatus = $apartamento->actualizarEstatusFinanciero();
    
    // Verificar estado DESPUÉS de actualizar estatus
    $apartamento->refresh();
    echo "\n=== DESPUÉS DE ACTUALIZAR ESTATUS ===\n";
    echo "Nuevo estatus: {$nuevoEstatus}\n";
    echo "Estatus en BD: {$apartamento->estatus_financiero}\n";
    echo "Saldo pendiente final: $" . number_format($apartamento->saldo_pendiente, 2) . "\n";
    
    // Contar recibos activos/vencidos restantes
    $recibosRestantes = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])
        ->whereHas('pagos', function($query) use ($apartamento) {
            $query->where('apartamento_id', $apartamento->id);
        })
        ->get()
        ->filter(function($recibo) use ($apartamento) {
            $totalPagado = $apartamento->pagos()
                ->where('recibo_gasto_comun_id', $recibo->id)
                ->where('estado', 'confirmado')
                ->sum('monto_pagado');
            return ($recibo->total_recibo - $totalPagado) > 0;
        })
        ->count();
    
    echo "Recibos con saldo pendiente restantes: {$recibosRestantes}\n";
    
    echo "\n=== ANÁLISIS DEL RESULTADO ===\n";
    if ($apartamento->saldo_pendiente == 0) {
        if ($apartamento->estatus_financiero === 'solvente') {
            echo "✅ CORRECTO: Apartamento solvente (saldo = 0)\n";
        } else {
            echo "❌ ERROR: Apartamento debería ser solvente pero es {$apartamento->estatus_financiero}\n";
        }
    } else {
        if ($recibosRestantes > 3) {
            if ($apartamento->estatus_financiero === 'moroso') {
                echo "✅ CORRECTO: Apartamento moroso (>3 recibos pendientes)\n";
            } else {
                echo "❌ ERROR: Apartamento debería ser moroso pero es {$apartamento->estatus_financiero}\n";
            }
        } else {
            if ($apartamento->estatus_financiero === 'deudor') {
                echo "✅ CORRECTO: Apartamento deudor (1-3 recibos pendientes)\n";
            } else {
                echo "❌ ERROR: Apartamento debería ser deudor pero es {$apartamento->estatus_financiero}\n";
            }
        }
    }
    
    // Rollback para no afectar los datos reales
    DB::rollback();
    echo "\n🔄 Transacción revertida (datos no afectados)\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DEL TEST ===\n";

?>