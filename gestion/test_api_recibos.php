<?php

require_once __DIR__ . '/vendor/autoload.php';

// Cargar la aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Configurar la aplicación
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

echo "=== PRUEBA DE API RECIBOS POR APARTAMENTO ===\n\n";

// Buscar apartamento 13
$apartamento = Apartamento::where('numero', '13')->first();

if (!$apartamento) {
    echo "❌ Apartamento 13 no encontrado\n";
    exit;
}

echo "✅ Apartamento encontrado: ID {$apartamento->id}, Número {$apartamento->numero}\n";
echo "Propietario: {$apartamento->propietario}\n\n";

// Simular la lógica del método getRecibosPorApartamento
echo "=== SIMULANDO LÓGICA DE getRecibosPorApartamento ===\n\n";

$apartamentoId = $apartamento->id;

try {
    // Obtener solo los recibos asignados al apartamento a través de la tabla pagos
    // Excluir pagos rechazados para evitar mostrar recibos desasignados
    $recibosAsignados = ReciboGastoComun::whereHas('pagos', function($query) use ($apartamentoId) {
            $query->where('apartamento_id', $apartamentoId)
                  ->where('estado', '!=', 'rechazado');
        })
        ->whereIn('estado', ['activo', 'vencido'])
        ->orderBy('fecha_vencimiento', 'asc')
        ->get();

    echo "Recibos asignados encontrados: {$recibosAsignados->count()}\n\n";

    $recibosConSaldo = collect();

    foreach ($recibosAsignados as $recibo) {
        // Calcular total pagado para este recibo y apartamento específico
        // Excluir pagos rechazados del cálculo
        $totalPagado = Pago::where('apartamento_id', $apartamentoId)
            ->where('recibo_gasto_comun_id', $recibo->id)
            ->where('estado', 'confirmado')
            ->sum('monto_pagado');
        
        $saldoPendiente = $recibo->total_recibo - $totalPagado;
        
        echo "Recibo: {$recibo->numero_recibo}\n";
        echo "  Total: $" . number_format($recibo->total_recibo, 2) . "\n";
        echo "  Pagado: $" . number_format($totalPagado, 2) . "\n";
        echo "  Saldo pendiente: $" . number_format($saldoPendiente, 2) . "\n";
        
        // Solo incluir recibos con saldo pendiente > 0
        if ($saldoPendiente > 0) {
            echo "  ✅ Incluido en la respuesta\n";
            $recibosConSaldo->push([
                'id' => $recibo->id,
                'numero_recibo' => $recibo->numero_recibo,
                'periodo' => $recibo->periodo,
                'total_recibo' => $recibo->total_recibo,
                'fecha_vencimiento' => $recibo->fecha_vencimiento,
                'saldo_pendiente' => $saldoPendiente
            ]);
        } else {
            echo "  ❌ Excluido (saldo = 0)\n";
        }
        echo "\n";
    }

    echo "=== RESULTADO FINAL ===\n";
    echo "Recibos con saldo pendiente: {$recibosConSaldo->count()}\n\n";

    if ($recibosConSaldo->count() > 0) {
        echo "JSON Response:\n";
        echo json_encode($recibosConSaldo, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "No hay recibos con saldo pendiente para este apartamento.\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n=== FIN DE LA PRUEBA ===\n";
?>