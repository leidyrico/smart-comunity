<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use Carbon\Carbon;

echo "=== PRUEBA: DEUDORES NUEVOS VS HISTÓRICOS ===\n\n";

// 1. Preparar datos de prueba
echo "1. PREPARACIÓN DE DATOS:\n";

// Crear un apartamento solvente para la prueba
$apartamentoSolvente = Apartamento::create([
    'numero' => '999',
    'piso' => 9,
    'torre' => 'A',
    'propietario' => 'Test Solvente',
    'telefono' => '1234567890',
    'email' => 'test@test.com',
    'area_m2' => 80.00,
    'tipo' => 'apartamento',
    'estado' => 'ocupado',
    'estatus_financiero' => 'solvente',
    'fecha_cambio_estatus' => null,
    'observaciones' => 'Apartamento de prueba'
]);

// Crear un apartamento deudor histórico (sin fecha de cambio)
$apartamentoDeudorHistorico = Apartamento::create([
    'numero' => '998',
    'piso' => 9,
    'torre' => 'A',
    'propietario' => 'Test Deudor Histórico',
    'telefono' => '1234567891',
    'email' => 'deudor@test.com',
    'area_m2' => 85.00,
    'tipo' => 'apartamento',
    'estado' => 'ocupado',
    'estatus_financiero' => 'deudor',
    'fecha_cambio_estatus' => null, // Sin fecha = deudor histórico
    'observaciones' => 'Apartamento deudor histórico'
]);

echo "Apartamento solvente creado: {$apartamentoSolvente->numero}\n";
echo "Apartamento deudor histórico creado: {$apartamentoDeudorHistorico->numero}\n";

// 2. Crear recibos de prueba
echo "\n2. CREANDO RECIBOS DE PRUEBA:\n";

// Recibo vencido
$reciboVencido = ReciboGastoComun::create([
    'numero_recibo' => 'TEST-VENCIDO-' . date('YmdHis'),
    'periodo' => 'Prueba Vencido',
    'fecha_emision' => Carbon::now()->subMonths(2),
    'fecha_vencimiento' => Carbon::now()->subMonth(),
    'valor_administracion' => 50000,
    'valor_aseo' => 15000,
    'valor_vigilancia' => 20000,
    'valor_mantenimiento' => 10000,
    'otros_conceptos' => 5000,
    'total_recibo' => 100000,
    'estado' => 'vencido',
    'observaciones' => 'Recibo vencido de prueba'
]);

// Recibo activo
$reciboActivo = ReciboGastoComun::create([
    'numero_recibo' => 'TEST-ACTIVO-' . date('YmdHis'),
    'periodo' => 'Prueba Activo',
    'fecha_emision' => Carbon::now(),
    'fecha_vencimiento' => Carbon::now()->addMonth(),
    'valor_administracion' => 60000,
    'valor_aseo' => 20000,
    'valor_vigilancia' => 25000,
    'valor_mantenimiento' => 15000,
    'otros_conceptos' => 5000,
    'total_recibo' => 125000,
    'estado' => 'activo',
    'observaciones' => 'Recibo activo de prueba'
]);

echo "Recibo vencido creado: {$reciboVencido->numero_recibo} ($100,000)\n";
echo "Recibo activo creado: {$reciboActivo->numero_recibo} ($125,000)\n";

// 3. Limpiar pagos existentes de apartamentos de prueba (si existen)
echo "\n3. LIMPIANDO PAGOS EXISTENTES:\n";
Pago::whereIn('apartamento_id', [$apartamentoSolvente->id, $apartamentoDeudorHistorico->id])->delete();
echo "Pagos existentes eliminados\n";

// 4. Asignar SOLO los recibos de prueba a los apartamentos
echo "\n4. ASIGNANDO RECIBOS DE PRUEBA:\n";

$apartamentosPrueba = [$apartamentoSolvente, $apartamentoDeudorHistorico];

foreach ($apartamentosPrueba as $apartamento) {
    // Asignar recibo vencido
    Pago::create([
        'recibo_gasto_comun_id' => $reciboVencido->id,
        'apartamento_id' => $apartamento->id,
        'monto_pagado' => 0,
        'fecha_pago' => null,
        'metodo_pago' => null,
        'numero_comprobante' => null,
        'observaciones' => 'Recibo vencido asignado - PRUEBA',
        'estado' => 'pendiente_confirmacion'
    ]);
    
    // Asignar recibo activo
    Pago::create([
        'recibo_gasto_comun_id' => $reciboActivo->id,
        'apartamento_id' => $apartamento->id,
        'monto_pagado' => 0,
        'fecha_pago' => null,
        'metodo_pago' => null,
        'numero_comprobante' => null,
        'observaciones' => 'Recibo activo asignado - PRUEBA',
        'estado' => 'pendiente_confirmacion'
    ]);
    
    echo "Recibos de prueba asignados a apartamento {$apartamento->numero}\n";
}

// 5. Cambiar el apartamento solvente a deudor (simulando creación de recibo activo)
echo "\n5. CAMBIANDO APARTAMENTO SOLVENTE A DEUDOR:\n";
$apartamentoSolvente->update([
    'estatus_financiero' => 'deudor',
    'fecha_cambio_estatus' => now()->toDateString() // Cambió hoy
]);
echo "Apartamento {$apartamentoSolvente->numero} cambiado a deudor con fecha de hoy\n";

// 6. Verificar saldos calculados
echo "\n6. VERIFICACIÓN DE SALDOS:\n";

// Refrescar datos
$apartamentoSolvente->refresh();
$apartamentoDeudorHistorico->refresh();

echo "\n--- Apartamento {$apartamentoSolvente->numero} (Ex-Solvente, Deudor desde hoy) ---\n";
echo "Estatus financiero: {$apartamentoSolvente->estatus_financiero}\n";
echo "Fecha cambio estatus: {$apartamentoSolvente->fecha_cambio_estatus}\n";
echo "Fecha actual: " . now()->toDateString() . "\n";
echo "¿Cambió hoy?: " . ($apartamentoSolvente->fecha_cambio_estatus === now()->toDateString() ? 'SÍ' : 'NO') . "\n";

// Debug: mostrar recibos asociados
$pagosDebug = $apartamentoSolvente->pagos()->with('reciboGastoComun')->get();
echo "Recibos asociados:\n";
foreach ($pagosDebug as $pagoDebug) {
    $reciboDebug = $pagoDebug->reciboGastoComun;
    echo "  - Recibo {$reciboDebug->numero_recibo}: {$reciboDebug->estado}, Total: $" . number_format($reciboDebug->total_recibo, 0, ',', '.') . "\n";
}

echo "Saldo pendiente: $" . number_format($apartamentoSolvente->saldo_pendiente, 0, ',', '.') . "\n";
echo "Explicación: Solo debe contar recibos ACTIVOS (no vencidos)\n";
echo "Esperado: $125,000 (solo recibo activo)\n";

echo "\n--- Apartamento {$apartamentoDeudorHistorico->numero} (Deudor Histórico) ---\n";
echo "Estatus financiero: {$apartamentoDeudorHistorico->estatus_financiero}\n";
echo "Fecha cambio estatus: " . ($apartamentoDeudorHistorico->fecha_cambio_estatus ?? 'NULL (histórico)') . "\n";
echo "Saldo pendiente: $" . number_format($apartamentoDeudorHistorico->saldo_pendiente, 0, ',', '.') . "\n";
echo "Explicación: Debe contar recibos ACTIVOS + VENCIDOS\n";
echo "Esperado: $225,000 (recibo activo + vencido)\n";

// 7. Validación detallada
echo "\n7. VALIDACIÓN DETALLADA:\n";

$validacionExitosa = true;

// Validar apartamento ex-solvente (deudor nuevo)
if ($apartamentoSolvente->saldo_pendiente == 125000) {
    echo "✅ CORRECTO: Ex-solvente solo cuenta recibos activos\n";
} else {
    echo "❌ ERROR: Ex-solvente debería tener $125,000 pero tiene $" . number_format($apartamentoSolvente->saldo_pendiente, 0, ',', '.') . "\n";
    $validacionExitosa = false;
}

// Validar apartamento deudor histórico
if ($apartamentoDeudorHistorico->saldo_pendiente == 225000) {
    echo "✅ CORRECTO: Deudor histórico cuenta recibos activos + vencidos\n";
} else {
    echo "❌ ERROR: Deudor histórico debería tener $225,000 pero tiene $" . number_format($apartamentoDeudorHistorico->saldo_pendiente, 0, ',', '.') . "\n";
    $validacionExitosa = false;
}

// 8. Limpiar datos de prueba
echo "\n8. LIMPIEZA:\n";

// Eliminar pagos
$pagosEliminados = Pago::whereIn('apartamento_id', [$apartamentoSolvente->id, $apartamentoDeudorHistorico->id])->delete();
echo "Pagos eliminados: {$pagosEliminados}\n";

// Eliminar recibos
$reciboVencido->delete();
$reciboActivo->delete();
echo "Recibos de prueba eliminados\n";

// Eliminar apartamentos
$apartamentoSolvente->delete();
$apartamentoDeudorHistorico->delete();
echo "Apartamentos de prueba eliminados\n";

echo "\n=== RESUMEN DE VALIDACIONES ===\n";
if ($validacionExitosa) {
    echo "✅ TODAS LAS VALIDACIONES EXITOSAS\n";
    echo "✅ Deudores nuevos (hoy): solo recibos activos\n";
    echo "✅ Deudores históricos: recibos activos + vencidos\n";
} else {
    echo "❌ ALGUNAS VALIDACIONES FALLARON\n";
}

echo "\n=== FIN DE LA PRUEBA ===\n";