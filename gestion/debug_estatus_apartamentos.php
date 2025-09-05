<?php

require_once __DIR__ . '/vendor/autoload.php';

// Cargar configuración de Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;

echo "=== DEBUG ESTATUS APARTAMENTOS ===\n\n";

// Obtener algunos apartamentos para analizar
$apartamentos = Apartamento::orderBy('numero')->take(10)->get();

echo "Analizando primeros 10 apartamentos...\n\n";

foreach ($apartamentos as $apartamento) {
    echo "--- APARTAMENTO {$apartamento->numero} ---\n";
    echo "Estatus actual: {$apartamento->estatus_financiero}\n";
    echo "Saldo pendiente (modelo): $" . number_format($apartamento->saldo_pendiente, 2) . "\n";
    
    // Verificar recibos asignados (excluyendo rechazados)
    $recibosAsignados = ReciboGastoComun::whereHas('pagos', function($query) use ($apartamento) {
        $query->where('apartamento_id', $apartamento->id)
              ->where('estado', '!=', 'rechazado');
    })->get();
    
    echo "Recibos asignados: {$recibosAsignados->count()}\n";
    
    $recibosConDeuda = 0;
    $saldoPendienteTotal = 0;
    
    foreach ($recibosAsignados as $recibo) {
        // Solo considerar recibos con monto > 0 (ignorar datos corruptos)
        if ($recibo->monto_total <= 0) {
            echo "  - Recibo {$recibo->numero_recibo}: IGNORADO (monto $0.00)\n";
            continue;
        }
        
        $totalPagado = $apartamento->pagos()
            ->where('recibo_gasto_comun_id', $recibo->id)
            ->where('estado', 'confirmado')
            ->sum('monto_pagado');
        
        $saldoRecibo = max(0, $recibo->monto_total - $totalPagado);
        
        if ($saldoRecibo > 0) {
            $recibosConDeuda++;
            $saldoPendienteTotal += $saldoRecibo;
            echo "  - Recibo {$recibo->numero_recibo}: $" . number_format($saldoRecibo, 2) . " pendiente\n";
        } else {
            echo "  - Recibo {$recibo->numero_recibo}: PAGADO\n";
        }
    }
    
    echo "Recibos con deuda real: {$recibosConDeuda}\n";
    echo "Saldo pendiente calculado: $" . number_format($saldoPendienteTotal, 2) . "\n";
    
    // Determinar estatus correcto
    if ($saldoPendienteTotal <= 0) {
        $estatusCorrect = 'solvente';
    } elseif ($recibosConDeuda >= 1 && $recibosConDeuda <= 3) {
        $estatusCorrect = 'deudor';
    } else {
        $estatusCorrect = 'moroso';
    }
    
    echo "Estatus correcto: {$estatusCorrect}\n";
    
    if ($apartamento->estatus_financiero !== $estatusCorrect) {
        echo "❌ INCONSISTENCIA DETECTADA\n";
    } else {
        echo "✅ Estatus correcto\n";
    }
    
    echo "\n";
}

// Verificar si hay apartamentos con recibos pendientes
echo "=== BÚSQUEDA DE APARTAMENTOS CON RECIBOS PENDIENTES ===\n\n";

$apartamentosConRecibos = Apartamento::whereHas('pagos', function($query) {
    $query->where('estado', '!=', 'rechazado');
})->get();

echo "Apartamentos con pagos asignados: {$apartamentosConRecibos->count()}\n\n";

$apartamentosConDeudaReal = 0;

foreach ($apartamentosConRecibos as $apartamento) {
    $recibosAsignados = ReciboGastoComun::whereHas('pagos', function($query) use ($apartamento) {
        $query->where('apartamento_id', $apartamento->id)
              ->where('estado', '!=', 'rechazado');
    })->get();
    
    $tieneDeudaReal = false;
    
    foreach ($recibosAsignados as $recibo) {
        if ($recibo->monto_total <= 0) continue;
        
        $totalPagado = $apartamento->pagos()
            ->where('recibo_gasto_comun_id', $recibo->id)
            ->where('estado', 'confirmado')
            ->sum('monto_pagado');
        
        $saldoRecibo = max(0, $recibo->monto_total - $totalPagado);
        
        if ($saldoRecibo > 0) {
            $tieneDeudaReal = true;
            break;
        }
    }
    
    if ($tieneDeudaReal) {
        $apartamentosConDeudaReal++;
        echo "Apartamento {$apartamento->numero}: TIENE DEUDA REAL (estatus: {$apartamento->estatus_financiero})\n";
    }
}

echo "\nApartamentos con deuda real: {$apartamentosConDeudaReal}\n";

if ($apartamentosConDeudaReal == 0) {
    echo "\n🎯 CONCLUSIÓN: Todos los apartamentos están realmente solventes.\n";
    echo "No hay apartamentos con recibos pendientes reales.\n";
} else {
    echo "\n⚠️ PROBLEMA: Hay apartamentos con deuda real pero marcados como solventes.\n";
}

echo "\n=== FIN DEBUG ===\n";

?>