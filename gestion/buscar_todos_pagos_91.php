<?php

require_once 'vendor/autoload.php';

// Configurar la aplicación Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\Pago;
use App\Models\ReciboGastoComun;
use Illuminate\Support\Facades\DB;

echo "=== BÚSQUEDA COMPLETA DE PAGOS APARTAMENTO 91 ===\n\n";

// Buscar apartamento 91
$apartamento = Apartamento::where('numero', '91')->first();
if (!$apartamento) {
    echo "❌ No se encontró el apartamento 91\n";
    exit;
}

echo "✅ Apartamento encontrado: {$apartamento->numero}\n";
echo "ID del apartamento: {$apartamento->id}\n\n";

// Buscar TODOS los pagos del apartamento 91
echo "=== TODOS LOS PAGOS HISTÓRICOS ===\n";
$todosPagos = Pago::where('apartamento_id', $apartamento->id)
    ->orderBy('created_at', 'desc')
    ->get();

if ($todosPagos->isEmpty()) {
    echo "❌ No se encontraron pagos para este apartamento\n";
} else {
    echo "✅ Total de pagos encontrados: " . $todosPagos->count() . "\n\n";
    
    foreach ($todosPagos as $pago) {
        $tipo = $pago->recibo_gasto_comun_id ? 'individual' : 'global';
        echo "📋 Pago ID: {$pago->id}\n";
        echo "   💰 Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
        echo "   📅 Fecha pago: {$pago->fecha_pago}\n";
        echo "   📅 Creado: {$pago->created_at}\n";
        echo "   🔄 Estado: {$pago->estado}\n";
        echo "   📝 Tipo: {$tipo}\n";
        echo "   📝 Método: {$pago->metodo_pago}\n";
        echo "   📄 Comprobante: {$pago->numero_comprobante}\n";
        echo "   📝 Observaciones: {$pago->observaciones}\n";
        
        if ($pago->recibo_gasto_comun_id) {
            $recibo = ReciboGastoComun::find($pago->recibo_gasto_comun_id);
            if ($recibo) {
                echo "   📋 Recibo asociado: {$recibo->numero_recibo}\n";
            }
        } else {
            echo "   📋 Recibo asociado: NINGUNO (pago global)\n";
        }
        echo "   " . str_repeat("-", 60) . "\n";
    }
}

// Buscar pagos de $100 en cualquier fecha
echo "\n=== PAGOS DE $100 (HISTÓRICO COMPLETO) ===\n";
$pagos100 = Pago::where('apartamento_id', $apartamento->id)
    ->where('monto_pagado', 100.00)
    ->orderBy('created_at', 'desc')
    ->get();

if ($pagos100->isEmpty()) {
    echo "❌ No se encontraron pagos de $100 en el historial completo\n";
} else {
    echo "✅ Pagos de $100 encontrados: " . $pagos100->count() . "\n\n";
    foreach ($pagos100 as $pago) {
        echo "📋 Pago ID: {$pago->id} - $" . number_format($pago->monto_pagado, 2) . " - {$pago->created_at} - Estado: {$pago->estado}\n";
    }
}

// Buscar pagos globales (sin recibo asociado)
echo "\n=== PAGOS GLOBALES (HISTÓRICO COMPLETO) ===\n";
$pagosGlobales = Pago::where('apartamento_id', $apartamento->id)
    ->whereNull('recibo_gasto_comun_id')
    ->orderBy('created_at', 'desc')
    ->get();

if ($pagosGlobales->isEmpty()) {
    echo "❌ No se encontraron pagos globales en el historial completo\n";
} else {
    echo "✅ Pagos globales encontrados: " . $pagosGlobales->count() . "\n\n";
    foreach ($pagosGlobales as $pago) {
        echo "📋 Pago Global ID: {$pago->id} - $" . number_format($pago->monto_pagado, 2) . " - {$pago->created_at} - Estado: {$pago->estado}\n";
        echo "   📄 Comprobante: {$pago->numero_comprobante}\n";
        echo "   📝 Observaciones: {$pago->observaciones}\n";
    }
}

// Verificar si hay pagos en estado rechazado
echo "\n=== PAGOS RECHAZADOS ===\n";
$pagosRechazados = Pago::where('apartamento_id', $apartamento->id)
    ->where('estado', 'rechazado')
    ->orderBy('created_at', 'desc')
    ->get();

if ($pagosRechazados->isEmpty()) {
    echo "❌ No se encontraron pagos rechazados\n";
} else {
    echo "✅ Pagos rechazados encontrados: " . $pagosRechazados->count() . "\n\n";
    foreach ($pagosRechazados as $pago) {
        echo "📋 Pago Rechazado ID: {$pago->id} - $" . number_format($pago->monto_pagado, 2) . " - {$pago->created_at}\n";
    }
}

echo "\n=== BÚSQUEDA COMPLETADA ===\n";