<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;

echo "=== PRUEBA: PRIMERA CARGA DE DATOS - APARTAMENTOS DEUDORES ===\n\n";

// 1. Crear apartamento deudor sin fecha_cambio_estatus (simula primera carga)
echo "1. Creando apartamento deudor sin fecha_cambio_estatus (primera carga)...\n";
// Generar número único para evitar conflictos
$numeroApartamento = '777-' . time();
$apartamento = Apartamento::create([
    'numero' => $numeroApartamento,
    'propietario' => 'Propietario Prueba',
    'tipo' => 'apartamento',
    'estatus_financiero' => 'deudor',
    'fecha_cambio_estatus' => null // Sin fecha = primera carga
]);
echo "   Apartamento {$apartamento->numero} creado como deudor sin fecha_cambio_estatus\n";

// 2. Crear recibos de prueba
echo "\n2. Creando recibos de prueba...\n";
$timestamp = time();
$reciboVencido = ReciboGastoComun::create([
    'numero_recibo' => 'REC-001-' . $timestamp,
    'periodo' => 'Enero 2024',
    'fecha_emision' => '2024-01-01',
    'fecha_vencimiento' => '2024-01-15',
    'valor_administracion' => 100000,
    'total_recibo' => 100000,
    'estado' => 'vencido'
]);
echo "   Recibo vencido creado: $100,000\n";

$reciboActivo = ReciboGastoComun::create([
    'numero_recibo' => 'REC-002-' . $timestamp,
    'periodo' => 'Febrero 2024',
    'fecha_emision' => '2024-02-01',
    'fecha_vencimiento' => '2024-02-15',
    'valor_administracion' => 75000,
    'total_recibo' => 75000,
    'estado' => 'activo'
]);
echo "   Recibo activo creado: $75,000\n";

// 3. Asignar recibos al apartamento
echo "\n3. Asignando recibos al apartamento...\n";
Pago::create([
    'apartamento_id' => $apartamento->id,
    'recibo_gasto_comun_id' => $reciboVencido->id,
    'monto_pagado' => 0,
    'estado' => 'pendiente_confirmacion'
]);
echo "   Recibo vencido asignado\n";

Pago::create([
    'apartamento_id' => $apartamento->id,
    'recibo_gasto_comun_id' => $reciboActivo->id,
    'monto_pagado' => 0,
    'estado' => 'pendiente_confirmacion'
]);
echo "   Recibo activo asignado\n";

// 4. Verificar saldo pendiente
echo "\n4. Verificando saldo pendiente...\n";
$saldoPendiente = $apartamento->fresh()->saldo_pendiente;
echo "   Saldo pendiente calculado: $" . number_format($saldoPendiente) . "\n";

// 5. Validación
echo "\n5. Validación...\n";
$saldoEsperado = 175000; // $100,000 (vencido) + $75,000 (activo)
if ($saldoPendiente == $saldoEsperado) {
    echo "   ✅ CORRECTO: Apartamento deudor de primera carga incluye recibos vencidos y activos\n";
    echo "   Esperado: $" . number_format($saldoEsperado) . ", Obtenido: $" . number_format($saldoPendiente) . "\n";
} else {
    echo "   ❌ ERROR: Saldo incorrecto\n";
    echo "   Esperado: $" . number_format($saldoEsperado) . ", Obtenido: $" . number_format($saldoPendiente) . "\n";
}

// 6. Información de debug
echo "\n6. Información de debug...\n";
echo "   Apartamento: {$apartamento->numero}\n";
echo "   Estatus financiero: {$apartamento->estatus_financiero}\n";
echo "   Fecha cambio estatus: " . ($apartamento->fecha_cambio_estatus ?? 'NULL (primera carga)') . "\n";
echo "   Fecha actual: " . now()->toDateString() . "\n";

$pagos = $apartamento->pagos()->with('reciboGastoComun')->get();
echo "   Recibos asociados:\n";
foreach ($pagos as $pago) {
    $recibo = $pago->reciboGastoComun;
    echo "     - {$recibo->periodo}: $" . number_format($recibo->total_recibo) . " ({$recibo->estado})\n";
}

// 7. Limpieza
echo "\n7. Limpiando datos de prueba...\n";
$apartamento->pagos()->delete();
$apartamento->delete();
$reciboVencido->delete();
$reciboActivo->delete();
echo "   Datos de prueba eliminados\n";

echo "\n=== FIN DE LA PRUEBA ===\n";