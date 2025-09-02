<?php

// Script para debuggear el problema con los recibos del apartamento 72

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

echo "=== DEBUG: Problema con recibos del apartamento 72 ===\n\n";

// 1. Verificar apartamento 72
$apartamento = Apartamento::where('numero', '72')->first();
if (!$apartamento) {
    echo "ERROR: No se encontró el apartamento 72\n";
    exit;
}

echo "Apartamento encontrado:\n";
echo "- ID: {$apartamento->id}\n";
echo "- Número: {$apartamento->numero}\n";
echo "- Propietario: {$apartamento->propietario}\n";
echo "- Estado: {$apartamento->estado_financiero}\n\n";

// 2. Verificar la consulta exacta que hace el controlador
echo "=== Consulta de recibos (igual que en PagoController) ===\n";

$recibos = ReciboGastoComun::where('apartamento_id', $apartamento->id)
    ->whereIn('estado', ['activo', 'vencido'])
    ->get()
    ->filter(function ($recibo) {
        $totalPagado = Pago::where('recibo_gasto_comun_id', $recibo->id)
            ->where('estado', 'confirmado')
            ->sum('monto_pagado');
        
        $saldoPendiente = $recibo->total_recibo - $totalPagado;
        return $saldoPendiente > 0;
    })
    ->map(function ($recibo) {
        $totalPagado = Pago::where('recibo_gasto_comun_id', $recibo->id)
            ->where('estado', 'confirmado')
            ->sum('monto_pagado');
        
        $saldoPendiente = $recibo->total_recibo - $totalPagado;
        
        return [
            'id' => $recibo->id,
            'numero_recibo' => $recibo->numero_recibo,
            'periodo' => $recibo->periodo,
            'total_recibo' => number_format($recibo->total_recibo, 2, '.', ''),
            'fecha_vencimiento' => $recibo->fecha_vencimiento,
            'saldo_pendiente' => $saldoPendiente
        ];
    })
    ->values();

echo "Recibos encontrados: " . count($recibos) . "\n";

foreach ($recibos as $recibo) {
    echo "\nRecibo ID: {$recibo['id']}\n";
    echo "- Número: {$recibo['numero_recibo']}\n";
    echo "- Período: {$recibo['periodo']}\n";
    echo "- Total: $" . $recibo['total_recibo'] . "\n";
    echo "- Saldo pendiente: $" . $recibo['saldo_pendiente'] . "\n";
    echo "- Vencimiento: {$recibo['fecha_vencimiento']}\n";
}

// 3. Verificar si hay algún problema con la respuesta JSON
echo "\n=== Simulando respuesta JSON ===\n";
$jsonResponse = json_encode($recibos->toArray());
echo "JSON generado: " . $jsonResponse . "\n";

// 4. Verificar si hay caracteres especiales o problemas de encoding
echo "\n=== Verificando encoding ===\n";
echo "JSON válido: " . (json_last_error() === JSON_ERROR_NONE ? 'SÍ' : 'NO') . "\n";
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Error JSON: " . json_last_error_msg() . "\n";
}

// 5. Verificar headers y configuración
echo "\n=== Verificando configuración ===\n";
echo "Timezone: " . date_default_timezone_get() . "\n";
echo "Locale: " . setlocale(LC_ALL, 0) . "\n";
echo "Encoding interno: " . mb_internal_encoding() . "\n";

echo "\n=== FIN DEBUG ===\n";