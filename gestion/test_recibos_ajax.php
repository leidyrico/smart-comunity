<?php

require_once __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;

echo "=== PRUEBA DE FUNCIONALIDAD AJAX RECIBOS ===\n\n";

// 1. Verificar que existen apartamentos
$apartamentos = Apartamento::all();
echo "Total apartamentos: " . $apartamentos->count() . "\n";

if ($apartamentos->count() > 0) {
    $apartamento = $apartamentos->first();
    echo "Apartamento de prueba: {$apartamento->numero} - {$apartamento->propietario}\n\n";
    
    // 2. Verificar recibos activos
    $recibosActivos = ReciboGastoComun::where('estado', 'activo')->get();
    echo "Total recibos activos: " . $recibosActivos->count() . "\n";
    
    // 3. Simular la consulta AJAX que hace getRecibosPorApartamento
    $apartamentoId = $apartamento->id;
    
    $recibosDisponibles = ReciboGastoComun::where('estado', 'activo')
        ->whereRaw('total_recibo > (SELECT COALESCE(SUM(monto_pagado), 0) FROM pagos WHERE recibo_gasto_comun_id = recibo_gasto_comuns.id AND apartamento_id = ? AND estado = "confirmado")', [$apartamentoId])
        ->orderBy('fecha_emision', 'desc')
        ->get(['id', 'numero_recibo', 'periodo', 'total_recibo', 'fecha_vencimiento']);
    
    echo "\nRecibos disponibles para apartamento {$apartamento->numero}:\n";
    echo "Total: " . $recibosDisponibles->count() . "\n";
    
    foreach ($recibosDisponibles as $recibo) {
        echo "- ID: {$recibo->id}, Número: {$recibo->numero_recibo}, Período: {$recibo->periodo}, Total: $" . number_format($recibo->total_recibo, 0, ',', '.') . "\n";
    }
    
    // 4. Verificar pagos existentes para este apartamento
    $pagos = Pago::where('apartamento_id', $apartamentoId)
                 ->where('estado', 'confirmado')
                 ->with('reciboGastoComun')
                 ->get();
    
    echo "\nPagos confirmados para apartamento {$apartamento->numero}:\n";
    echo "Total: " . $pagos->count() . "\n";
    
    foreach ($pagos as $pago) {
        $recibo = $pago->reciboGastoComun;
        $reciboInfo = $recibo ? $recibo->numero_recibo : 'Sin recibo';
        echo "- Recibo: {$reciboInfo}, Monto: $" . number_format($pago->monto_pagado, 0, ',', '.') . "\n";
    }
    
} else {
    echo "No hay apartamentos en la base de datos.\n";
}

echo "\n=== FIN DE LA PRUEBA ===\n";