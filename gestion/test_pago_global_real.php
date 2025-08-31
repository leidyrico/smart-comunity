<?php

require_once __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pago;
use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Http\Controllers\PagoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

echo "=== PRUEBA DE PAGO GLOBAL REAL ===\n\n";

// Buscar un apartamento con recibos pendientes
$apartamentos = Apartamento::all();
$apartamentoConPendientes = null;

foreach ($apartamentos as $apartamento) {
    $recibos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])->get();
    $tieneRecibosConSaldo = false;
    
    foreach ($recibos as $recibo) {
        $totalPagado = Pago::where('apartamento_id', $apartamento->id)
            ->where('recibo_gasto_comun_id', $recibo->id)
            ->where('estado', 'confirmado')
            ->sum('monto_pagado');
        
        $saldoPendiente = $recibo->total_recibo - $totalPagado;
        
        if ($saldoPendiente > 0) {
            $tieneRecibosConSaldo = true;
            break;
        }
    }
    
    if ($tieneRecibosConSaldo) {
        $apartamentoConPendientes = $apartamento;
        break;
    }
}

if (!$apartamentoConPendientes) {
    echo "❌ No se encontró ningún apartamento con recibos pendientes para probar\n";
    exit(1);
}

echo "Apartamento seleccionado para prueba: {$apartamentoConPendientes->numero} - {$apartamentoConPendientes->propietario}\n\n";

// Obtener estado antes del pago
echo "=== ESTADO ANTES DEL PAGO ===\n";
$pagosAntes = Pago::where('apartamento_id', $apartamentoConPendientes->id)->count();
echo "Pagos registrados: {$pagosAntes}\n";

// Calcular recibos pendientes
$recibos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])
    ->orderBy('fecha_vencimiento', 'asc')
    ->get();

$recibosConSaldo = collect();
$totalPendienteAntes = 0;

foreach ($recibos as $recibo) {
    $totalPagado = Pago::where('apartamento_id', $apartamentoConPendientes->id)
        ->where('recibo_gasto_comun_id', $recibo->id)
        ->where('estado', 'confirmado')
        ->sum('monto_pagado');
    
    $saldoPendiente = $recibo->total_recibo - $totalPagado;
    
    if ($saldoPendiente > 0) {
        $recibo->saldo_pendiente_calculado = $saldoPendiente;
        $recibosConSaldo->push($recibo);
        $totalPendienteAntes += $saldoPendiente;
    }
}

echo "Recibos con saldo pendiente: {$recibosConSaldo->count()}\n";
echo "Total pendiente: $" . number_format($totalPendienteAntes, 2) . "\n\n";

// Simular el pago global
$montoPago = 50.00; // Pagar $50
echo "=== EJECUTANDO PAGO GLOBAL DE $" . number_format($montoPago, 2) . " ===\n\n";

try {
    DB::beginTransaction();
    
    // Simular la lógica del método storeGlobal
    $montoRestante = $montoPago;
    $pagosCreados = [];
    
    foreach ($recibosConSaldo as $recibo) {
        if ($montoRestante <= 0) break;
        
        $saldoPendiente = $recibo->saldo_pendiente_calculado;
        $montoPagar = min($montoRestante, $saldoPendiente);
        
        // Crear el pago
        $pago = Pago::create([
            'apartamento_id' => $apartamentoConPendientes->id,
            'recibo_gasto_comun_id' => $recibo->id,
            'monto_pagado' => $montoPagar,
            'fecha_pago' => date('Y-m-d'),
            'metodo_pago' => 'transferencia',
            'numero_comprobante' => 'TEST-' . date('YmdHis'),
            'observaciones' => 'Pago global de prueba - distribuido automáticamente',
            'estado' => 'confirmado'
        ]);
        
        $pagosCreados[] = $pago;
        $montoRestante -= $montoPagar;
        
        echo "✅ Pago creado: ID {$pago->id} - Recibo {$recibo->numero_recibo} - $" . number_format($montoPagar, 2) . "\n";
    }
    
    // Si queda monto restante, asociarlo al recibo más reciente
    if ($montoRestante > 0 && $recibosConSaldo->count() > 0) {
        $reciboMasReciente = $recibos->where('estado', '!=', 'rechazado')
            ->sortByDesc('fecha_vencimiento')
            ->first();
        
        if ($reciboMasReciente) {
            $pagoExtra = Pago::create([
                'apartamento_id' => $apartamentoConPendientes->id,
                'recibo_gasto_comun_id' => $reciboMasReciente->id,
                'monto_pagado' => $montoRestante,
                'fecha_pago' => date('Y-m-d'),
                'metodo_pago' => 'transferencia',
                'numero_comprobante' => 'TEST-' . date('YmdHis') . '-EXTRA',
                'observaciones' => 'Pago global de prueba - monto restante asignado al recibo más reciente',
                'estado' => 'confirmado'
            ]);
            
            $pagosCreados[] = $pagoExtra;
            echo "✅ Pago extra creado: ID {$pagoExtra->id} - Recibo {$reciboMasReciente->numero_recibo} - $" . number_format($montoRestante, 2) . "\n";
        }
    }
    
    DB::commit();
    
    echo "\n✅ PAGO GLOBAL EJECUTADO EXITOSAMENTE\n";
    echo "Pagos creados: " . count($pagosCreados) . "\n\n";
    
    // Verificar estado después del pago
    echo "=== ESTADO DESPUÉS DEL PAGO ===\n";
    $pagosDespues = Pago::where('apartamento_id', $apartamentoConPendientes->id)->count();
    echo "Pagos registrados: {$pagosDespues}\n";
    echo "Nuevos pagos creados: " . ($pagosDespues - $pagosAntes) . "\n";
    
    // Recalcular saldo pendiente
    $totalPendienteDespues = 0;
    $recibosConSaldoDespues = 0;
    
    foreach ($recibos as $recibo) {
        $totalPagado = Pago::where('apartamento_id', $apartamentoConPendientes->id)
            ->where('recibo_gasto_comun_id', $recibo->id)
            ->where('estado', 'confirmado')
            ->sum('monto_pagado');
        
        $saldoPendiente = $recibo->total_recibo - $totalPagado;
        
        if ($saldoPendiente > 0) {
            $recibosConSaldoDespues++;
            $totalPendienteDespues += $saldoPendiente;
        }
    }
    
    echo "Recibos con saldo pendiente: {$recibosConSaldoDespues}\n";
    echo "Total pendiente: $" . number_format($totalPendienteDespues, 2) . "\n";
    echo "Reducción del saldo: $" . number_format($totalPendienteAntes - $totalPendienteDespues, 2) . "\n";
    
    if (abs(($totalPendienteAntes - $totalPendienteDespues) - $montoPago) < 0.01) {
        echo "✅ VERIFICACIÓN EXITOSA: El saldo se redujo exactamente en el monto pagado\n";
    } else {
        echo "⚠️  ADVERTENCIA: Discrepancia en la reducción del saldo\n";
    }
    
} catch (Exception $e) {
    DB::rollback();
    echo "❌ ERROR: No se pudo ejecutar el pago global\n";
    echo "Mensaje: {$e->getMessage()}\n";
    echo "Archivo: {$e->getFile()}:{$e->getLine()}\n";
}

echo "\n=== FIN DE LA PRUEBA ===\n";
?>