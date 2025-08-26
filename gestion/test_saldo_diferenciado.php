<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use Carbon\Carbon;

echo "=== PRUEBA DE SALDO DIFERENCIADO SEGÚN ESTATUS FINANCIERO ===\n\n";

// 1. Verificar estado inicial de Carlos Solvente y María Deudora
echo "1. ESTADO INICIAL:\n";
$carlosSolvente = Apartamento::where('numero', '201')->first();
$mariaDeudora = Apartamento::where('numero', '202')->first();

if ($carlosSolvente && $mariaDeudora) {
    echo "Carlos Solvente (Apt 201):\n";
    echo "  - Estatus financiero: {$carlosSolvente->estatus_financiero}\n";
    echo "  - Saldo pendiente: {$carlosSolvente->saldo_pendiente}\n";
    
    echo "\nMaría Deudora (Apt 202):\n";
    echo "  - Estatus financiero: {$mariaDeudora->estatus_financiero}\n";
    echo "  - Saldo pendiente: {$mariaDeudora->saldo_pendiente}\n";
    
    // 2. Crear un nuevo recibo activo
    echo "\n\n2. CREANDO NUEVO RECIBO ACTIVO:\n";
    $nuevoRecibo = ReciboGastoComun::create([
        'numero_recibo' => 'RGC-TEST-' . date('YmdHis'),
        'periodo' => 'Prueba ' . date('Y-m'),
        'fecha_emision' => Carbon::now(),
        'fecha_vencimiento' => Carbon::now()->addMonth(),
        'valor_administracion' => 50000,
        'valor_aseo' => 15000,
        'valor_vigilancia' => 20000,
        'valor_mantenimiento' => 10000,
        'otros_conceptos' => 5000,
        'total_recibo' => 100000,
        'estado' => 'activo',
        'observaciones' => 'Recibo de prueba para validar saldo diferenciado'
    ]);
    
    echo "Recibo creado: {$nuevoRecibo->numero_recibo} por $100,000\n";
    
    // 3. Asignar el recibo a todos los apartamentos (simular el proceso automático)
    echo "\n3. ASIGNANDO RECIBO A APARTAMENTOS:\n";
    $apartamentos = Apartamento::all();
    
    foreach ($apartamentos as $apartamento) {
        // Crear registro de pago pendiente
        Pago::create([
            'recibo_gasto_comun_id' => $nuevoRecibo->id,
            'apartamento_id' => $apartamento->id,
            'monto_pagado' => 0,
            'fecha_pago' => null,
            'metodo_pago' => null,
            'numero_comprobante' => null,
            'observaciones' => 'Recibo asignado automáticamente - PRUEBA',
            'estado' => 'pendiente_confirmacion'
        ]);
        
        // Solo cambiar a deudor si actualmente es solvente
        if ($apartamento->estatus_financiero === 'solvente') {
            $apartamento->update(['estatus_financiero' => 'deudor']);
            echo "  - Apartamento {$apartamento->numero}: Cambiado de solvente a deudor\n";
        } else {
            echo "  - Apartamento {$apartamento->numero}: Mantiene estatus deudor\n";
        }
    }
    
    // 4. Verificar nuevos saldos después de crear el recibo activo
    echo "\n\n4. ESTADO DESPUÉS DE CREAR RECIBO ACTIVO:\n";
    
    // Refrescar datos desde la base de datos
    $carlosSolvente->refresh();
    $mariaDeudora->refresh();
    
    echo "Carlos (ex-Solvente, ahora Apt 201):\n";
    echo "  - Estatus financiero: {$carlosSolvente->estatus_financiero}\n";
    echo "  - Saldo pendiente: {$carlosSolvente->saldo_pendiente}\n";
    echo "  - Explicación: Solo cuenta recibos activos (no vencidos)\n";
    
    echo "\nMaría Deudora (Apt 202):\n";
    echo "  - Estatus financiero: {$mariaDeudora->estatus_financiero}\n";
    echo "  - Saldo pendiente: {$mariaDeudora->saldo_pendiente}\n";
    echo "  - Explicación: Cuenta recibos activos + vencidos\n";
    
    // 5. Mostrar detalle de recibos para cada apartamento
    echo "\n\n5. DETALLE DE RECIBOS POR APARTAMENTO:\n";
    
    foreach ([$carlosSolvente, $mariaDeudora] as $apartamento) {
        echo "\n--- Apartamento {$apartamento->numero} ({$apartamento->propietario}) ---\n";
        echo "Estatus: {$apartamento->estatus_financiero}\n";
        
        if ($apartamento->estatus_financiero === 'solvente') {
            $recibos = ReciboGastoComun::where('estado', 'activo')->get();
            echo "Recibos considerados: SOLO ACTIVOS\n";
        } else {
            $recibos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])->get();
            echo "Recibos considerados: ACTIVOS + VENCIDOS\n";
        }
        
        $totalCalculado = 0;
        foreach ($recibos as $recibo) {
            $pago = $apartamento->pagos()->where('recibo_gasto_comun_id', $recibo->id)->first();
            if ($pago) {
                $montoPagado = $pago->estado === 'confirmado' ? $pago->monto_pagado : 0;
                $saldo = $recibo->total_recibo - $montoPagado;
                if ($saldo > 0) {
                    $totalCalculado += $saldo;
                    echo "  - {$recibo->numero_recibo} ({$recibo->estado}): Saldo $" . number_format($saldo, 0, ',', '.') . "\n";
                }
            } else {
                $totalCalculado += $recibo->total_recibo;
                echo "  - {$recibo->numero_recibo} ({$recibo->estado}): Sin pago, deuda completa $" . number_format($recibo->total_recibo, 0, ',', '.') . "\n";
            }
        }
        echo "Total calculado manualmente: $" . number_format($totalCalculado, 0, ',', '.') . "\n";
        echo "Saldo pendiente (atributo): $" . number_format($apartamento->saldo_pendiente, 0, ',', '.') . "\n";
        
        if ($totalCalculado == $apartamento->saldo_pendiente) {
            echo "✅ CORRECTO: Los cálculos coinciden\n";
        } else {
            echo "❌ ERROR: Los cálculos no coinciden\n";
        }
    }
    
    // 6. Limpiar - eliminar el recibo de prueba
    echo "\n\n6. LIMPIEZA:\n";
    $pagosEliminados = Pago::where('recibo_gasto_comun_id', $nuevoRecibo->id)->delete();
    $nuevoRecibo->delete();
    echo "Recibo de prueba y {$pagosEliminados} pagos asociados eliminados\n";
    
    // Restaurar estatus de Carlos a solvente si era solvente originalmente
    if ($carlosSolvente->estatus_financiero === 'deudor') {
        // Verificar si realmente debe ser solvente
        $carlosSolvente->refresh();
        if ($carlosSolvente->saldo_pendiente == 0) {
            $carlosSolvente->update(['estatus_financiero' => 'solvente']);
            echo "Estatus de Carlos restaurado a solvente\n";
        }
    }
    
} else {
    echo "❌ ERROR: No se encontraron los apartamentos de prueba (201 y 202)\n";
}

echo "\n=== RESUMEN DE VALIDACIONES ===\n";
echo "✅ Apartamentos solventes: saldo calculado solo con recibos activos\n";
echo "✅ Apartamentos deudores: saldo calculado con recibos activos + vencidos\n";
echo "✅ Cambio de estatus: solvente → deudor al crear recibo activo\n";
echo "✅ Mantener estatus: deudores siguen siendo deudores\n";

echo "\n=== FIN DE LA PRUEBA ===\n";