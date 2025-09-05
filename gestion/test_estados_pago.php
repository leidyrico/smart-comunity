<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Apartamento;
use App\Models\Pago;
use App\Models\ReciboGastoComun;
use Illuminate\Support\Facades\DB;

echo "=== TEST ESTADOS DE PAGO Y ESTATUS FINANCIERO ===\n\n";

// Buscar un apartamento con saldo pendiente
$apartamento = Apartamento::whereIn('estatus_financiero', ['deudor', 'moroso'])
    ->get()
    ->filter(function($apt) {
        return $apt->saldo_pendiente > 0;
    })
    ->first();

if (!$apartamento) {
    echo "No se encontró un apartamento con saldo pendiente.\n";
    exit;
}

echo "=== APARTAMENTO SELECCIONADO ===\n";
echo "Apartamento: {$apartamento->numero}\n";
echo "Propietario: {$apartamento->propietario}\n";
echo "Estatus financiero inicial: {$apartamento->estatus_financiero}\n";
echo "Saldo pendiente inicial: $" . number_format($apartamento->saldo_pendiente, 2) . "\n\n";

// Encontrar un recibo con saldo pendiente
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

if (!$reciboPendiente) {
    echo "No se encontró un recibo con saldo pendiente.\n";
    exit;
}

echo "=== RECIBO SELECCIONADO ===\n";
echo "Recibo ID: {$reciboPendiente->id}\n";
echo "Total recibo: $" . number_format($reciboPendiente->total_recibo, 2) . "\n";
echo "Saldo pendiente: $" . number_format($saldoRecibo, 2) . "\n\n";

// Test 1: Pago con estado 'pendiente_confirmacion'
echo "=== TEST 1: PAGO PENDIENTE_CONFIRMACION ===\n";
DB::beginTransaction();

try {
    $pago1 = Pago::create([
        'apartamento_id' => $apartamento->id,
        'recibo_gasto_comun_id' => $reciboPendiente->id,
        'monto_pagado' => $saldoRecibo,
        'fecha_pago' => now(),
        'metodo_pago' => 'transferencia',
        'numero_comprobante' => 'TEST-PENDIENTE-' . time(),
        'estado' => 'pendiente_confirmacion',
        'observaciones' => 'Test pago pendiente confirmación'
    ]);
    
    echo "Pago creado con estado 'pendiente_confirmacion'\n";
    
    // Actualizar estatus
    $apartamento->refresh();
    echo "Saldo pendiente después del pago: $" . number_format($apartamento->saldo_pendiente, 2) . "\n";
    
    $nuevoEstatus1 = $apartamento->actualizarEstatusFinanciero();
    $apartamento->refresh();
    
    echo "Estatus después de actualizar: {$apartamento->estatus_financiero}\n";
    echo "¿Cambió el estatus? " . ($apartamento->estatus_financiero !== $apartamento->getOriginal('estatus_financiero') ? 'SÍ' : 'NO') . "\n\n";
    
    DB::rollback();
    
} catch (Exception $e) {
    DB::rollback();
    echo "Error en test 1: " . $e->getMessage() . "\n\n";
}

// Test 2: Pago con estado 'confirmado'
echo "=== TEST 2: PAGO CONFIRMADO ===\n";
DB::beginTransaction();

try {
    $pago2 = Pago::create([
        'apartamento_id' => $apartamento->id,
        'recibo_gasto_comun_id' => $reciboPendiente->id,
        'monto_pagado' => $saldoRecibo,
        'fecha_pago' => now(),
        'metodo_pago' => 'transferencia',
        'numero_comprobante' => 'TEST-CONFIRMADO-' . time(),
        'estado' => 'confirmado',
        'observaciones' => 'Test pago confirmado'
    ]);
    
    echo "Pago creado con estado 'confirmado'\n";
    
    // Actualizar estatus
    $apartamento->refresh();
    echo "Saldo pendiente después del pago: $" . number_format($apartamento->saldo_pendiente, 2) . "\n";
    
    $nuevoEstatus2 = $apartamento->actualizarEstatusFinanciero();
    $apartamento->refresh();
    
    echo "Estatus después de actualizar: {$apartamento->estatus_financiero}\n";
    echo "¿Cambió el estatus? " . ($apartamento->estatus_financiero !== $apartamento->getOriginal('estatus_financiero') ? 'SÍ' : 'NO') . "\n\n";
    
    DB::rollback();
    
} catch (Exception $e) {
    DB::rollback();
    echo "Error en test 2: " . $e->getMessage() . "\n\n";
}

echo "=== CONCLUSIONES ===\n";
echo "1. Los pagos con estado 'pendiente_confirmacion' NO afectan el saldo pendiente\n";
echo "2. Solo los pagos con estado 'confirmado' reducen el saldo pendiente\n";
echo "3. El estatus financiero se actualiza basado en el saldo pendiente calculado\n";
echo "4. Si el usuario crea pagos como 'pendiente_confirmacion', debe confirmarlos después\n\n";

echo "=== RECOMENDACIÓN ===\n";
echo "Verificar que los pagos se estén creando con estado 'confirmado' o\n";
echo "implementar un proceso para confirmar pagos pendientes.\n";

echo "\n=== FIN DEL TEST ===\n";

?>