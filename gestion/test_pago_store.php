<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\PagoController;
use App\Models\Pago;
use App\Models\Apartamento;
use App\Models\ReciboGastoComun;

echo "=== PRUEBA DE REGISTRO DE PAGO (store) ===\n\n";

// Seleccionar apartamento y recibo válidos
$apartamento = Apartamento::where('numero', '24')->first();
$recibo = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])->orderBy('fecha_emision', 'desc')->first();

if (!$apartamento || !$recibo) {
    echo "❌ No se encontró apartamento 24 o recibo válido\n";
    exit(1);
}

echo "Apartamento: ID {$apartamento->id}, Número {$apartamento->numero}\n";
echo "Recibo: ID {$recibo->id}, Número {$recibo->numero_recibo}, Estado {$recibo->estado}\n\n";

$controller = new PagoController();

$payload = [
    'apartamento_id' => $apartamento->id,
    'recibo_gasto_comun_id' => $recibo->id,
    'monto_pagado' => 5.00,
    'monto_en_bs' => null,
    'fecha_pago' => date('Y-m-d'),
    'metodo_pago' => 'efectivo',
    'numero_comprobante' => 'TEST-' . uniqid(),
    'observaciones' => 'Prueba automática de store()',
    'estado' => 'confirmado',
    // Filtros opcionales para la redirección
    'numero_apartamento' => $apartamento->numero
];

try {
    $request = Request::create('/pagos', 'POST', $payload);
    $response = $controller->store($request);
    echo "✅ store() ejecutado. Tipo de respuesta: " . get_class($response) . "\n";
    
    // Mostrar último pago creado
    $ultimoPago = Pago::orderBy('id', 'desc')->first();
    if ($ultimoPago) {
        echo "\n=== ÚLTIMO PAGO CREADO ===\n";
        echo "ID: {$ultimoPago->id}\n";
        echo "Apartamento: {$ultimoPago->apartamento_id}\n";
        echo "Recibo: {$ultimoPago->recibo_gasto_comun_id}\n";
        echo "Monto: $" . number_format((float)$ultimoPago->monto_pagado, 2) . "\n";
        echo "Fecha: {$ultimoPago->fecha_pago}\n";
        echo "Estado: {$ultimoPago->estado}\n";
        echo "Comprobante: {$ultimoPago->numero_comprobante}\n";
    } else {
        echo "❌ No se encontró pago recién creado\n";
    }
} catch (\Throwable $e) {
    echo "❌ Error ejecutando store(): " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Traza:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n=== FIN DE PRUEBA ===\n";