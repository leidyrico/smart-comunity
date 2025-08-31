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

echo "=== INVESTIGACIÓN PAGO $100 - APARTAMENTO 13 ===\n\n";

// Buscar apartamento 13 (que tiene ID 91 en la base de datos)
echo "=== VERIFICANDO APARTAMENTO 13 ===\n";
$apartamento13 = Apartamento::where('numero', '13')->first();
if (!$apartamento13) {
    echo "❌ No se encontró el apartamento 13\n";
    exit;
}

echo "✅ Apartamento encontrado:\n";
echo "   ID: {$apartamento13->id}\n";
echo "   Número: {$apartamento13->numero}\n";
echo "   Propietario: {$apartamento13->propietario}\n\n";

// Buscar TODOS los pagos del apartamento 13
echo "=== TODOS LOS PAGOS DEL APARTAMENTO 13 ===\n";
$todosPagos = Pago::where('apartamento_id', $apartamento13->id)
    ->orderBy('created_at', 'desc')
    ->get();

echo "Total de pagos encontrados: " . $todosPagos->count() . "\n\n";

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
    
    // Destacar si es un pago de $100
    if ($pago->monto_pagado == 100.00) {
        echo "   🎯 ¡ESTE ES EL PAGO DE $100!\n";
    }
    
    echo "   " . str_repeat("-", 60) . "\n";
}

// Buscar específicamente pagos de $100
echo "\n=== PAGOS DE $100 EN APARTAMENTO 13 ===\n";
$pagos100 = Pago::where('apartamento_id', $apartamento13->id)
    ->where('monto_pagado', 100.00)
    ->orderBy('created_at', 'desc')
    ->get();

if ($pagos100->isEmpty()) {
    echo "❌ No se encontraron pagos de $100\n";
} else {
    echo "✅ Pagos de $100 encontrados: " . $pagos100->count() . "\n\n";
    foreach ($pagos100 as $pago) {
        echo "🎯 PAGO DE $100 ENCONTRADO:\n";
        echo "   📋 ID: {$pago->id}\n";
        echo "   💰 Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
        echo "   📅 Fecha: {$pago->fecha_pago}\n";
        echo "   📅 Creado: {$pago->created_at}\n";
        echo "   🔄 Estado: {$pago->estado}\n";
        echo "   📄 Comprobante: {$pago->numero_comprobante}\n";
        echo "   📝 Observaciones: {$pago->observaciones}\n";
        
        if ($pago->recibo_gasto_comun_id) {
            $recibo = ReciboGastoComun::find($pago->recibo_gasto_comun_id);
            if ($recibo) {
                echo "   📋 Recibo: {$recibo->numero_recibo} (Monto: $" . number_format($recibo->monto, 2) . ")\n";
            }
        } else {
            echo "   📋 PAGO GLOBAL - Se distribuyó automáticamente\n";
        }
        echo "   " . str_repeat("=", 60) . "\n";
    }
}

// Buscar pagos globales
echo "\n=== PAGOS GLOBALES EN APARTAMENTO 13 ===\n";
$pagosGlobales = Pago::where('apartamento_id', $apartamento13->id)
    ->whereNull('recibo_gasto_comun_id')
    ->orderBy('created_at', 'desc')
    ->get();

if ($pagosGlobales->isEmpty()) {
    echo "❌ No se encontraron pagos globales\n";
} else {
    echo "✅ Pagos globales encontrados: " . $pagosGlobales->count() . "\n\n";
    foreach ($pagosGlobales as $pago) {
        echo "🌐 PAGO GLOBAL:\n";
        echo "   📋 ID: {$pago->id}\n";
        echo "   💰 Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
        echo "   📅 Fecha: {$pago->fecha_pago}\n";
        echo "   📅 Creado: {$pago->created_at}\n";
        echo "   🔄 Estado: {$pago->estado}\n";
        echo "   📄 Comprobante: {$pago->numero_comprobante}\n";
        echo "   📝 Observaciones: {$pago->observaciones}\n";
        echo "   " . str_repeat("=", 60) . "\n";
    }
}

echo "\n=== INVESTIGACIÓN COMPLETADA ===\n";
echo "\n💡 EXPLICACIÓN:\n";
echo "El apartamento 91 (Angela Jimenez) tiene ID 66 en la base de datos.\n";
echo "El apartamento 13 tiene ID 91 en la base de datos.\n";
echo "Parece que hubo una confusión en el sistema de numeración.\n";
echo "Los pagos se registraron para el apartamento con ID 91 (número 13).\n";