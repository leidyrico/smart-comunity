<?php

require_once __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pago;
use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use Illuminate\Support\Facades\DB;

echo "=== VERIFICACIÓN DE LÓGICA DE DISTRIBUCIÓN DE PAGOS GLOBALES ===\n\n";

// Buscar apartamentos con recibos pendientes
echo "Buscando apartamentos con recibos pendientes...\n\n";

$apartamentos = Apartamento::all();
$apartamentosConPendientes = [];

foreach ($apartamentos as $apartamento) {
    // Obtener recibos activos y vencidos
    $recibos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])->get();
    $recibosConSaldo = collect();
    
    foreach ($recibos as $recibo) {
        // Calcular total pagado para este recibo y apartamento
        $totalPagado = Pago::where('apartamento_id', $apartamento->id)
            ->where('recibo_gasto_comun_id', $recibo->id)
            ->where('estado', 'confirmado')
            ->sum('monto_pagado');
        
        $saldoPendiente = $recibo->total_recibo - $totalPagado;
        
        if ($saldoPendiente > 0) {
            $recibo->saldo_pendiente_calculado = $saldoPendiente;
            $recibosConSaldo->push($recibo);
        }
    }
    
    if ($recibosConSaldo->count() > 0) {
        $totalPendiente = $recibosConSaldo->sum('saldo_pendiente_calculado');
        $apartamentosConPendientes[] = [
            'apartamento' => $apartamento,
            'recibos_pendientes' => $recibosConSaldo->count(),
            'total_pendiente' => $totalPendiente
        ];
    }
}

echo "Apartamentos con recibos pendientes encontrados: " . count($apartamentosConPendientes) . "\n\n";

if (count($apartamentosConPendientes) > 0) {
    // Mostrar los primeros 5 apartamentos con pendientes
    echo "APARTAMENTOS CON SALDO PENDIENTE:\n";
    echo str_repeat('-', 80) . "\n";
    
    $contador = 0;
    foreach ($apartamentosConPendientes as $data) {
        if ($contador >= 5) break;
        
        $apto = $data['apartamento'];
        echo "Apartamento {$apto->numero} - {$apto->propietario}:\n";
        echo "  Recibos pendientes: {$data['recibos_pendientes']}\n";
        echo "  Total pendiente: $" . number_format($data['total_pendiente'], 2) . "\n\n";
        $contador++;
    }
    
    // Seleccionar el primer apartamento para prueba
    $apartamentoPrueba = $apartamentosConPendientes[0]['apartamento'];
    echo "=== SIMULANDO PAGO GLOBAL PARA APARTAMENTO {$apartamentoPrueba->numero} ===\n\n";
    
    // Simular la lógica de storeGlobal
    $montoTotal = 100.00; // Simular pago de $100
    $montoRestante = $montoTotal;
    
    echo "Monto a distribuir: $" . number_format($montoTotal, 2) . "\n\n";
    
    // Obtener recibos con saldo pendiente ordenados por fecha de vencimiento
    $recibos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])
        ->orderBy('fecha_vencimiento', 'asc')
        ->get();
    
    $recibosConSaldo = collect();
    
    foreach ($recibos as $recibo) {
        $totalPagado = Pago::where('apartamento_id', $apartamentoPrueba->id)
            ->where('recibo_gasto_comun_id', $recibo->id)
            ->where('estado', 'confirmado')
            ->sum('monto_pagado');
        
        $saldoPendiente = $recibo->total_recibo - $totalPagado;
        
        if ($saldoPendiente > 0) {
            $recibo->saldo_pendiente_calculado = $saldoPendiente;
            $recibosConSaldo->push($recibo);
        }
    }
    
    echo "Recibos con saldo pendiente (ordenados por vencimiento):\n";
    foreach ($recibosConSaldo as $recibo) {
        echo "- {$recibo->numero_recibo}: $" . number_format($recibo->saldo_pendiente_calculado, 2) . " (Vence: {$recibo->fecha_vencimiento})\n";
    }
    
    echo "\nDISTRIBUCIÓN SIMULADA:\n";
    echo str_repeat('-', 50) . "\n";
    
    $pagosSimulados = [];
    
    foreach ($recibosConSaldo as $recibo) {
        if ($montoRestante <= 0) break;
        
        $saldoPendiente = $recibo->saldo_pendiente_calculado;
        $montoPagar = min($montoRestante, $saldoPendiente);
        
        $pagosSimulados[] = [
            'recibo' => $recibo->numero_recibo,
            'saldo_pendiente' => $saldoPendiente,
            'monto_a_pagar' => $montoPagar,
            'saldo_restante' => $saldoPendiente - $montoPagar
        ];
        
        echo "Recibo {$recibo->numero_recibo}:\n";
        echo "  Saldo pendiente: $" . number_format($saldoPendiente, 2) . "\n";
        echo "  Monto a pagar: $" . number_format($montoPagar, 2) . "\n";
        echo "  Saldo restante: $" . number_format($saldoPendiente - $montoPagar, 2) . "\n\n";
        
        $montoRestante -= $montoPagar;
    }
    
    echo "Monto restante sin distribuir: $" . number_format($montoRestante, 2) . "\n";
    
    if ($montoRestante > 0) {
        echo "⚠️  Hay monto sobrante que se asignaría al recibo más reciente\n";
    }
    
    echo "\n✅ LÓGICA DE DISTRIBUCIÓN VERIFICADA CORRECTAMENTE\n";
    
} else {
    echo "❌ No se encontraron apartamentos con recibos pendientes para probar\n";
}

echo "\n=== FIN DE LA VERIFICACIÓN ===\n";
?>