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

echo "=== BÚSQUEDA PAGO COMPROBANTE 672832207870 ===\n\n";

// Buscar apartamento 13 (Mairyn Burgos)
echo "=== VERIFICANDO APARTAMENTO 13 (MAIRYN BURGOS) ===\n";
$apartamento13 = Apartamento::where('numero', '13')->first();
if (!$apartamento13) {
    echo "❌ No se encontró el apartamento 13\n";
    exit;
}

echo "✅ Apartamento encontrado:\n";
echo "   ID: {$apartamento13->id}\n";
echo "   Número: {$apartamento13->numero}\n";
echo "   Propietario: {$apartamento13->propietario}\n\n";

// Buscar por número de comprobante específico
echo "=== BÚSQUEDA POR COMPROBANTE 672832207870 ===\n";
$pagoComprobante = Pago::where('numero_comprobante', '672832207870')->first();

if (!$pagoComprobante) {
    echo "❌ No se encontró pago con comprobante 672832207870\n";
    
    // Buscar comprobantes similares
    echo "\n=== BÚSQUEDA DE COMPROBANTES SIMILARES ===\n";
    $comprobantesSimilares = Pago::where('numero_comprobante', 'LIKE', '%672832207870%')
        ->orWhere('numero_comprobante', 'LIKE', '%672832%')
        ->get();
    
    if ($comprobantesSimilares->isEmpty()) {
        echo "❌ No se encontraron comprobantes similares\n";
    } else {
        echo "✅ Comprobantes similares encontrados: " . $comprobantesSimilares->count() . "\n\n";
        foreach ($comprobantesSimilares as $pago) {
            echo "📋 Pago ID: {$pago->id} - Comprobante: {$pago->numero_comprobante}\n";
            echo "   💰 Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
            echo "   🏠 Apartamento ID: {$pago->apartamento_id}\n";
            echo "   📅 Fecha: {$pago->fecha_pago}\n";
            echo "   🔄 Estado: {$pago->estado}\n";
            echo "   " . str_repeat("-", 50) . "\n";
        }
    }
} else {
    echo "✅ ¡PAGO ENCONTRADO!\n\n";
    echo "📋 Pago ID: {$pagoComprobante->id}\n";
    echo "💰 Monto: $" . number_format($pagoComprobante->monto_pagado, 2) . "\n";
    echo "📅 Fecha pago: {$pagoComprobante->fecha_pago}\n";
    echo "📅 Creado: {$pagoComprobante->created_at}\n";
    echo "🔄 Estado: {$pagoComprobante->estado}\n";
    echo "📄 Comprobante: {$pagoComprobante->numero_comprobante}\n";
    echo "📝 Método: {$pagoComprobante->metodo_pago}\n";
    echo "📝 Observaciones: {$pagoComprobante->observaciones}\n";
    echo "🏠 Apartamento ID: {$pagoComprobante->apartamento_id}\n";
    
    // Verificar a qué apartamento pertenece
    $apartamentoPago = Apartamento::find($pagoComprobante->apartamento_id);
    if ($apartamentoPago) {
        echo "🏠 Apartamento: {$apartamentoPago->numero} - {$apartamentoPago->propietario}\n";
    }
    
    // Verificar si tiene recibo asociado
    if ($pagoComprobante->recibo_gasto_comun_id) {
        $recibo = ReciboGastoComun::find($pagoComprobante->recibo_gasto_comun_id);
        if ($recibo) {
            echo "📋 Recibo asociado: {$recibo->numero_recibo}\n";
            echo "💰 Monto recibo: $" . number_format($recibo->monto, 2) . "\n";
        }
    } else {
        echo "📋 PAGO GLOBAL - Sin recibo específico asociado\n";
    }
}

// Buscar todos los pagos del apartamento 13 con comprobantes que contengan '672832'
echo "\n=== PAGOS DEL APARTAMENTO 13 CON COMPROBANTES SIMILARES ===\n";
$pagosApt13 = Pago::where('apartamento_id', $apartamento13->id)
    ->where('numero_comprobante', 'LIKE', '%672832%')
    ->orderBy('created_at', 'desc')
    ->get();

if ($pagosApt13->isEmpty()) {
    echo "❌ No se encontraron pagos del apartamento 13 con comprobantes similares\n";
} else {
    echo "✅ Pagos encontrados: " . $pagosApt13->count() . "\n\n";
    foreach ($pagosApt13 as $pago) {
        echo "📋 Pago ID: {$pago->id}\n";
        echo "   💰 Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
        echo "   📅 Fecha: {$pago->fecha_pago}\n";
        echo "   📅 Creado: {$pago->created_at}\n";
        echo "   🔄 Estado: {$pago->estado}\n";
        echo "   📄 Comprobante: {$pago->numero_comprobante}\n";
        echo "   📝 Observaciones: {$pago->observaciones}\n";
        
        if ($pago->recibo_gasto_comun_id) {
            $recibo = ReciboGastoComun::find($pago->recibo_gasto_comun_id);
            if ($recibo) {
                echo "   📋 Recibo: {$recibo->numero_recibo} ($" . number_format($recibo->monto, 2) . ")\n";
            }
        } else {
            echo "   📋 PAGO GLOBAL\n";
        }
        echo "   " . str_repeat("-", 60) . "\n";
    }
}

// Verificar si el pago se distribuyó automáticamente
if (isset($pagoComprobante) && $pagoComprobante->monto_pagado == 100.00) {
    echo "\n=== ANÁLISIS DEL PAGO DE $100 ===\n";
    echo "🎯 Este es el pago de $100 que buscabas\n";
    echo "📊 Estado del pago: {$pagoComprobante->estado}\n";
    
    if ($pagoComprobante->recibo_gasto_comun_id) {
        echo "📋 El pago se asignó al recibo específico\n";
    } else {
        echo "🌐 El pago fue procesado como PAGO GLOBAL\n";
        echo "💡 Esto significa que se distribuyó automáticamente entre los recibos pendientes\n";
    }
}

echo "\n=== BÚSQUEDA COMPLETADA ===\n";