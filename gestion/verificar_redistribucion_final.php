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

echo "=== VERIFICACIÓN FINAL DE LA REDISTRIBUCIÓN ===\n\n";

// Buscar apartamento 13
$apartamento = Apartamento::where('numero', 13)->first();
if (!$apartamento) {
    echo "❌ No se encontró el apartamento 13\n";
    exit;
}

echo "✅ Apartamento: {$apartamento->numero} - {$apartamento->propietario}\n\n";

// Verificar pagos con comprobante 672832207870
echo "=== ESTADO ACTUAL DEL PAGO DE $100 ===\n";
$pagosActivos = Pago::where('numero_comprobante', '672832207870')
    ->where('apartamento_id', $apartamento->id)
    ->where('estado', 'confirmado')
    ->get();

if ($pagosActivos->isEmpty()) {
    echo "❌ No se encontraron pagos activos con el comprobante 672832207870\n";
} else {
    echo "✅ Pagos activos encontrados: " . $pagosActivos->count() . "\n";
    echo "💰 Monto total: $" . number_format($pagosActivos->sum('monto_pagado'), 2) . "\n\n";
    
    foreach ($pagosActivos as $pago) {
    $recibo = $pago->recibo_gasto_comun_id ? ReciboGastoComun::find($pago->recibo_gasto_comun_id) : null;
    
    if ($recibo) {
        // Pago específico a un recibo
        echo "📋 Pago ID {$pago->id}: $" . number_format($pago->monto_pagado, 2) . " → {$recibo->numero_recibo}\n";
        echo "   📅 Fecha vencimiento: {$recibo->fecha_vencimiento}\n";
        echo "   💰 Monto recibo: $" . number_format($recibo->total_recibo, 2) . "\n";
        echo "   🔄 Estado recibo: {$recibo->estado}\n";
    } else {
        // Pago global
        echo "💳 Pago ID {$pago->id}: $" . number_format($pago->monto_pagado, 2) . " → PAGO GLOBAL\n";
        echo "   📅 Fecha pago: {$pago->fecha_pago}\n";
        echo "   📄 Comprobante: {$pago->numero_comprobante}\n";
        echo "   🔄 Estado: {$pago->estado}\n";
    }
    echo "   " . str_repeat("-", 50) . "\n";
}
}

// Verificar si REC-0922 y REC-1022 son problemáticos
echo "\n=== VERIFICACIÓN DE RECIBOS PROBLEMÁTICOS ===\n";
$recibosProblematicos = ['REC-0922', 'REC-1022'];

foreach ($recibosProblematicos as $numeroRecibo) {
    $recibo = ReciboGastoComun::where('numero_recibo', $numeroRecibo)->first();
    if ($recibo) {
        echo "📋 {$numeroRecibo} (ID: {$recibo->id}):\n";
        echo "   📅 Fecha vencimiento: {$recibo->fecha_vencimiento}\n";
        echo "   💰 Monto: $" . number_format($recibo->total_recibo, 2) . "\n";
        echo "   🔄 Estado: {$recibo->estado}\n";
        
        // Verificar si tiene pagos del apartamento 13
        $pagoApartamento13 = Pago::where('apartamento_id', $apartamento->id)
            ->where('recibo_gasto_comun_id', $recibo->id)
            ->where('estado', 'confirmado')
            ->first();
        
        if ($pagoApartamento13) {
            echo "   ✅ TIENE pago confirmado del apartamento 13: $" . number_format($pagoApartamento13->monto_pagado, 2) . "\n";
        } else {
            echo "   ❌ NO tiene pagos confirmados del apartamento 13\n";
        }
        
        // Verificar si tiene pagos de otros apartamentos
        $pagosOtrosApartamentos = Pago::where('recibo_gasto_comun_id', $recibo->id)
            ->where('apartamento_id', '!=', $apartamento->id)
            ->where('estado', 'confirmado')
            ->get();
        
        if ($pagosOtrosApartamentos->count() > 0) {
            echo "   ⚠️  TIENE pagos de otros apartamentos:\n";
            foreach ($pagosOtrosApartamentos as $pagoOtro) {
                $otroApartamento = Apartamento::find($pagoOtro->apartamento_id);
                echo "      🏠 Apartamento {$otroApartamento->numero}: $" . number_format($pagoOtro->monto_pagado, 2) . "\n";
            }
        } else {
            echo "   ✅ NO tiene pagos de otros apartamentos\n";
        }
        
        echo "   " . str_repeat("=", 60) . "\n";
    }
}

// Simular la vista de deudas del apartamento 13
echo "\n=== SIMULACIÓN DE LA VISTA DE DEUDAS ===\n";

// Obtener recibos con pagos activos (simulando DeudaController::show)
$recibosConPagos = DB::table('recibo_gasto_comuns')
    ->join('pagos', 'recibo_gasto_comuns.id', '=', 'pagos.recibo_gasto_comun_id')
    ->where('pagos.apartamento_id', $apartamento->id)
    ->where('pagos.estado', '!=', 'rechazado')
    ->whereIn('recibo_gasto_comuns.estado', ['activo', 'vencido'])
    ->select('recibo_gasto_comuns.*')
    ->distinct()
    ->orderBy('recibo_gasto_comuns.fecha_vencimiento', 'asc')
    ->get();

echo "📊 Total de recibos en la vista: " . $recibosConPagos->count() . "\n";

// Verificar si REC-0922 y REC-1022 aparecen
$rec0922EnVista = false;
$rec1022EnVista = false;

foreach ($recibosConPagos as $recibo) {
    if ($recibo->numero_recibo === 'REC-0922') {
        $rec0922EnVista = true;
        echo "⚠️  REC-0922 APARECE en la vista\n";
    }
    if ($recibo->numero_recibo === 'REC-1022') {
        $rec1022EnVista = true;
        echo "⚠️  REC-1022 APARECE en la vista\n";
    }
}

if (!$rec0922EnVista) {
    echo "✅ REC-0922 NO aparece en la vista\n";
}
if (!$rec1022EnVista) {
    echo "✅ REC-1022 NO aparece en la vista\n";
}

// Verificar REC-0723 como último recibo
$rec0723 = null;
foreach ($recibosConPagos as $recibo) {
    if ($recibo->numero_recibo === 'REC-0723') {
        $rec0723 = $recibo;
        break;
    }
}

if ($rec0723) {
    echo "✅ REC-0723 encontrado en la vista\n";
} else {
    echo "❌ REC-0723 NO encontrado en la vista\n";
}

echo "\n=== RESUMEN FINAL ===\n";
echo "💰 Pago de $100 redistribuido: " . ($pagosActivos->count() > 0 ? "✅ SÍ" : "❌ NO") . "\n";
echo "📊 Recibos en vista: " . $recibosConPagos->count() . " (Esperado: 20)\n";
echo "🎯 REC-0922 en vista: " . ($rec0922EnVista ? "⚠️  SÍ (PROBLEMA)" : "✅ NO") . "\n";
echo "🎯 REC-1022 en vista: " . ($rec1022EnVista ? "⚠️  SÍ (PROBLEMA)" : "✅ NO") . "\n";
echo "🎯 REC-0723 en vista: " . ($rec0723 ? "✅ SÍ" : "❌ NO") . "\n";

if ($rec0922EnVista || $rec1022EnVista) {
    echo "\n⚠️  ADVERTENCIA: Los recibos problemáticos siguen apareciendo en la vista\n";
    echo "💡 Esto significa que el problema original persiste\n";
    echo "💡 Recomendación: Verificar si estos recibos realmente corresponden al apartamento 13\n";
} else {
    echo "\n🎉 ÉXITO: Los recibos problemáticos ya no aparecen en la vista\n";
}

echo "\n=== VERIFICACIÓN COMPLETADA ===\n";