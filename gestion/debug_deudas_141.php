<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

echo "=== DEBUG DEUDAS CONTROLADOR APARTAMENTO 141 ===\n\n";

// Simular exactamente la lógica del controlador
$numeroApartamento = 141;
$nombrePropietario = '';
$estadoDeuda = '';
$numeroRecibo = '';

echo "Parámetros de búsqueda:\n";
echo "- Número apartamento: {$numeroApartamento}\n";
echo "- Nombre propietario: '{$nombrePropietario}'\n";
echo "- Estado deuda: '{$estadoDeuda}'\n";
echo "- Número recibo: '{$numeroRecibo}'\n\n";

// 1. Obtener apartamentos que tienen pagos
echo "1. OBTENIENDO APARTAMENTOS CON PAGOS...\n";
$apartamentos = Apartamento::whereHas('pagos')
    ->with(['pagos.reciboGastoComun'])
    ->get();

echo "Total apartamentos con pagos: {$apartamentos->count()}\n\n";

// 2. Obtener recibos activos y vencidos
echo "2. OBTENIENDO RECIBOS...\n";
$recibosActivos = ReciboGastoComun::where('estado', 'activo')
    ->whereHas('pagos')
    ->orderBy('fecha_emision', 'desc')
    ->get();
    
$recibosVencidos = ReciboGastoComun::where('estado', 'vencido')
    ->whereHas('pagos')
    ->orderBy('fecha_emision', 'asc')
    ->get();

echo "Recibos activos: {$recibosActivos->count()}\n";
echo "Recibos vencidos: {$recibosVencidos->count()}\n\n";

$todosRecibos = $recibosActivos->merge($recibosVencidos);
echo "Total recibos: {$todosRecibos->count()}\n\n";

// 3. Procesar datos de deuda
echo "3. PROCESANDO DATOS DE DEUDA...\n";
$datosDeuda = [];

foreach ($apartamentos as $apartamento) {
    foreach ($todosRecibos as $recibo) {
        // Verificar si este apartamento tiene pagos para este recibo
        $pagosApartamento = $apartamento->pagos->where('recibo_gasto_comun_id', $recibo->id);
        
        if ($pagosApartamento->isEmpty()) {
            continue;
        }
        
        // Calcular monto pagado confirmado
        $montoPagado = $pagosApartamento->where('estado', 'confirmado')->sum('monto_pagado');
        
        // Calcular saldo actual
        $saldoActual = $recibo->total_recibo - $montoPagado;
        
        // Obtener fecha de pago más reciente
        $fechaPago = $pagosApartamento->where('estado', 'confirmado')
            ->where('monto_pagado', '>', 0)
            ->max('fecha_pago');
        
        $datosDeuda[] = [
            'propietario' => $apartamento->propietario,
            'numero_apartamento' => $apartamento->numero,
            'numero_recibo' => $recibo->numero_recibo,
            'fecha_facturacion' => $recibo->fecha_emision,
            'monto_facturado' => $recibo->total_recibo,
            'monto_pagado' => $montoPagado,
            'fecha_pago' => $fechaPago,
            'saldo_actual' => $saldoActual,
            'apartamento_id' => $apartamento->id,
            'recibo_id' => $recibo->id
        ];
    }
}

echo "Total registros generados: " . count($datosDeuda) . "\n\n";

// 4. Aplicar filtros
echo "4. APLICANDO FILTROS...\n";

// Filtro por número de apartamento
if ($numeroApartamento) {
    $datosDeuda = array_filter($datosDeuda, function($dato) use ($numeroApartamento) {
        return $dato['numero_apartamento'] == $numeroApartamento;
    });
    echo "Después de filtrar por apartamento {$numeroApartamento}: " . count($datosDeuda) . " registros\n";
}

// Filtro por nombre de propietario
if ($nombrePropietario) {
    $datosDeuda = array_filter($datosDeuda, function($dato) use ($nombrePropietario) {
        return stripos($dato['propietario'], $nombrePropietario) !== false;
    });
    echo "Después de filtrar por propietario: " . count($datosDeuda) . " registros\n";
}

// Filtro por estado de deuda
if ($estadoDeuda) {
    if ($estadoDeuda === 'pendiente') {
        $datosDeuda = array_filter($datosDeuda, function($dato) {
            return $dato['saldo_actual'] > 0;
        });
    } elseif ($estadoDeuda === 'pagado') {
        $datosDeuda = array_filter($datosDeuda, function($dato) {
            return $dato['saldo_actual'] <= 0;
        });
    }
    echo "Después de filtrar por estado de deuda: " . count($datosDeuda) . " registros\n";
}

// Filtro por número de recibo
if ($numeroRecibo) {
    $datosDeuda = array_filter($datosDeuda, function($dato) use ($numeroRecibo) {
        return stripos($dato['numero_recibo'], $numeroRecibo) !== false;
    });
    echo "Después de filtrar por número de recibo: " . count($datosDeuda) . " registros\n";
}

echo "\n5. RESULTADOS FINALES PARA APARTAMENTO 141:\n";
echo "Total registros después de filtros: " . count($datosDeuda) . "\n\n";

foreach ($datosDeuda as $dato) {
    echo "Recibo: {$dato['numero_recibo']}\n";
    echo "Propietario: {$dato['propietario']}\n";
    echo "Apartamento: {$dato['numero_apartamento']}\n";
    echo "Fecha facturación: {$dato['fecha_facturacion']}\n";
    echo "Monto facturado: {$dato['monto_facturado']}\n";
    echo "Monto pagado: {$dato['monto_pagado']}\n";
    echo "Saldo actual: {$dato['saldo_actual']}\n";
    echo "Fecha pago: " . ($dato['fecha_pago'] ?: 'Sin pago') . "\n";
    echo "---\n";
}

echo "\n=== FIN DEL DEBUG ===\n";
?>