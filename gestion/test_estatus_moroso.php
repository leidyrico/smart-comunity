<?php

require_once __DIR__ . '/vendor/autoload.php';

// Cargar configuración de Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use Carbon\Carbon;

echo "=== PRUEBA DEL NUEVO ESTATUS FINANCIERO 'MOROSO' ===\n\n";

// 1. Crear apartamento de prueba
echo "1. Creando apartamento de prueba...\n";
$apartamento = Apartamento::create([
    'numero' => 'TEST-MOROSO',
    'piso' => 1,
    'torre' => 'A',
    'propietario' => 'Juan Moroso Test',
    'telefono' => '3001234567',
    'email' => 'test@moroso.com',
    'metros_cuadrados' => 80,
    'tipo' => 'apartamento',
    'estado' => 'ocupado',
    'estatus_financiero' => 'solvente'
]);
echo "Apartamento creado: {$apartamento->numero}\n\n";

// 2. Crear 5 recibos vencidos
echo "2. Creando 5 recibos vencidos...\n";
$recibos = [];
for ($i = 1; $i <= 5; $i++) {
    $recibo = ReciboGastoComun::create([
        'numero_recibo' => 'REC-MOROSO-' . str_pad($i, 3, '0', STR_PAD_LEFT),
        'periodo' => Carbon::now()->subMonths($i)->format('Y-m'),
        'fecha_emision' => Carbon::now()->subMonths($i)->startOfMonth(),
        'fecha_vencimiento' => Carbon::now()->subMonths($i)->addDays(15),
        'valor_administracion' => 100000,
        'valor_aseo' => 20000,
        'valor_vigilancia' => 30000,
        'valor_mantenimiento' => 15000,
        'otros_conceptos' => 5000,
        'total_recibo' => 170000,
        'estado' => 'vencido'
    ]);
    
    // Crear pago parcial o sin pago para generar deuda
    Pago::create([
        'apartamento_id' => $apartamento->id,
        'recibo_gasto_comun_id' => $recibo->id,
        'monto_pagado' => $i <= 2 ? 0 : 50000, // Primeros 2 sin pago, otros con pago parcial
        'fecha_pago' => Carbon::now(),
        'estado' => 'confirmado'
    ]);
    
    $recibos[] = $recibo;
    echo "Recibo {$recibo->numero_recibo} creado (vencido)\n";
}
echo "\n";

// 3. Actualizar estatus financiero
echo "3. Actualizando estatus financiero...\n";
$nuevoEstatus = $apartamento->actualizarEstatusFinanciero();
echo "Nuevo estatus: {$nuevoEstatus}\n";

// 4. Verificar resultado
echo "\n4. Verificando resultado...\n";
$apartamento->refresh();
echo "Apartamento {$apartamento->numero}:\n";
echo "  - Estatus financiero: {$apartamento->estatus_financiero}\n";
echo "  - Saldo pendiente: \${$apartamento->saldo_pendiente}\n";

// Contar recibos vencidos pendientes
$recibosVencidosPendientes = 0;
foreach ($recibos as $recibo) {
    $pago = $apartamento->pagos()->where('recibo_gasto_comun_id', $recibo->id)->first();
    if ($pago) {
        $saldo = $recibo->total_recibo - $pago->monto_pagado;
        if ($saldo > 0) {
            $recibosVencidosPendientes++;
        }
    } else {
        $recibosVencidosPendientes++;
    }
}

echo "  - Recibos vencidos pendientes: {$recibosVencidosPendientes}\n";

// 5. Verificar lógica
echo "\n5. Verificando lógica...\n";
if ($recibosVencidosPendientes > 3 && $apartamento->estatus_financiero === 'moroso') {
    echo "✅ CORRECTO: Apartamento con {$recibosVencidosPendientes} recibos vencidos es 'moroso'\n";
} elseif ($recibosVencidosPendientes <= 3 && $apartamento->estatus_financiero === 'deudor') {
    echo "✅ CORRECTO: Apartamento con {$recibosVencidosPendientes} recibos vencidos es 'deudor'\n";
} elseif ($apartamento->saldo_pendiente == 0 && $apartamento->estatus_financiero === 'solvente') {
    echo "✅ CORRECTO: Apartamento sin deuda es 'solvente'\n";
} else {
    echo "❌ ERROR: Lógica incorrecta\n";
    echo "   Recibos vencidos: {$recibosVencidosPendientes}\n";
    echo "   Estatus actual: {$apartamento->estatus_financiero}\n";
    echo "   Saldo pendiente: {$apartamento->saldo_pendiente}\n";
}

// 6. Probar métodos del modelo
echo "\n6. Probando métodos del modelo...\n";
echo "  - esSolvente(): " . ($apartamento->esSolvente() ? 'true' : 'false') . "\n";
echo "  - esDeudor(): " . ($apartamento->esDeudor() ? 'true' : 'false') . "\n";
echo "  - esMoroso(): " . ($apartamento->esMoroso() ? 'true' : 'false') . "\n";
echo "  - tieneDeudas(): " . ($apartamento->tieneDeudas() ? 'true' : 'false') . "\n";

// 7. Limpiar datos de prueba
echo "\n7. Limpiando datos de prueba...\n";
Pago::where('apartamento_id', $apartamento->id)->delete();
foreach ($recibos as $recibo) {
    $recibo->delete();
}
$apartamento->delete();
echo "Datos de prueba eliminados.\n";

echo "\n=== FIN DE PRUEBA ===\n";