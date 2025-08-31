<?php

require_once __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use Illuminate\Support\Facades\DB;

echo "=== VERIFICACIÓN PAGO GLOBAL APARTAMENTO 91 ===\n\n";

try {
    // Buscar apartamento 91
    $apartamento = Apartamento::find(91);
    if (!$apartamento) {
        echo "ERROR: Apartamento 91 no encontrado\n";
        exit(1);
    }
    
    echo "Apartamento encontrado: {$apartamento->numero} - {$apartamento->propietario}\n\n";
    
    // Simular la lógica del método createGlobal del PagoController
    echo "=== SIMULANDO LÓGICA createGlobal ===\n";
    
    // Obtener todos los recibos activos y vencidos
    $recibos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])
        ->orderBy('fecha_vencimiento', 'asc')
        ->get();
    
    echo "Total de recibos activos/vencidos en el sistema: " . $recibos->count() . "\n";
    
    $recibos_pendientes = collect();
    
    foreach ($recibos as $recibo) {
        // Calcular el total pagado para este recibo por este apartamento
        $total_pagado = DB::table('pagos')
            ->where('apartamento_id', $apartamento->id)
            ->where('recibo_gasto_comun_id', $recibo->id)
            ->sum('monto_pagado');
        
        // Calcular el saldo pendiente
        $saldo_pendiente = $recibo->total - $total_pagado;
        
        if ($saldo_pendiente > 0) {
            $recibo->saldo_pendiente_apartamento = $saldo_pendiente;
            $recibos_pendientes->push($recibo);
        }
    }
    
    echo "Recibos con saldo pendiente para apartamento 91: " . $recibos_pendientes->count() . "\n\n";
    
    if ($recibos_pendientes->count() > 0) {
        echo "=== RECIBOS PENDIENTES ===\n";
        $total_pendiente = 0;
        
        foreach ($recibos_pendientes as $recibo) {
            echo "ID: {$recibo->id} | Número: {$recibo->numero_recibo} | ";
            echo "Total: $" . number_format($recibo->total, 2) . " | ";
            echo "Pendiente: $" . number_format($recibo->saldo_pendiente_apartamento, 2) . "\n";
            $total_pendiente += $recibo->saldo_pendiente_apartamento;
        }
        
        echo "\nTOTAL PENDIENTE: $" . number_format($total_pendiente, 2) . "\n";
    } else {
        echo "No hay recibos pendientes para este apartamento.\n";
    }
    
    // Verificar si hay algún problema con la vista
    echo "\n=== VERIFICACIÓN ADICIONAL ===\n";
    
    // Verificar todos los recibos del sistema
    $todos_recibos = ReciboGastoComun::all();
    echo "Total de recibos en el sistema: " . $todos_recibos->count() . "\n";
    
    // Verificar pagos del apartamento 91
    $pagos_apartamento = DB::table('pagos')
        ->where('apartamento_id', 91)
        ->get();
    echo "Total de pagos registrados para apartamento 91: " . $pagos_apartamento->count() . "\n";
    
    if ($pagos_apartamento->count() > 0) {
        echo "\nÚltimos pagos del apartamento 91:\n";
        foreach ($pagos_apartamento->take(5) as $pago) {
            echo "- Monto: $" . number_format($pago->monto_pagado, 2) . " | Fecha: {$pago->fecha_pago} | Recibo ID: {$pago->recibo_gasto_comun_id}\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n=== FIN VERIFICACIÓN ===\n";
?>