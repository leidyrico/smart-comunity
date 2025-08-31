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

echo "=== INVESTIGACIÓN PAGO GLOBAL DE $100 ===\n\n";

// Buscar apartamento 91
$apartamento = Apartamento::where('numero', '91')->first();
if (!$apartamento) {
    echo "❌ No se encontró el apartamento 91\n";
    exit;
}

echo "✅ Apartamento encontrado: {$apartamento->numero}\n";
echo "ID del apartamento: {$apartamento->id}\n\n";

// Buscar pagos globales recientes (últimos 30 días)
// Los pagos globales tienen recibo_gasto_comun_id NULL
echo "=== PAGOS GLOBALES RECIENTES (ÚLTIMOS 30 DÍAS) ===\n";
$pagosGlobales = Pago::where('apartamento_id', $apartamento->id)
    ->whereNull('recibo_gasto_comun_id')
    ->where('created_at', '>=', now()->subDays(30))
    ->orderBy('created_at', 'desc')
    ->get();

if ($pagosGlobales->isEmpty()) {
    echo "❌ No se encontraron pagos globales recientes\n";
} else {
    foreach ($pagosGlobales as $pago) {
        echo "📋 Pago ID: {$pago->id}\n";
        echo "   💰 Monto: $" . number_format($pago->monto, 2) . "\n";
        echo "   📅 Fecha: {$pago->fecha_pago}\n";
        echo "   📅 Creado: {$pago->created_at}\n";
        echo "   🔄 Estado: {$pago->estado}\n";
        echo "   📝 Método: {$pago->metodo_pago}\n";
        echo "   📄 Comprobante: {$pago->numero_comprobante}\n";
        echo "   🏠 Apartamento: {$pago->apartamento_id}\n";
        
        // Verificar si hay recibo asociado
        if ($pago->recibo_gasto_comun_id) {
            $recibo = ReciboGastoComun::find($pago->recibo_gasto_comun_id);
            if ($recibo) {
                echo "   📋 Recibo asociado: {$recibo->numero_recibo}\n";
            }
        } else {
            echo "   📋 Recibo asociado: NINGUNO (pago global)\n";
        }
        echo "   " . str_repeat("-", 50) . "\n";
    }
}

// Buscar todos los pagos recientes (no solo globales)
echo "\n=== TODOS LOS PAGOS RECIENTES (ÚLTIMOS 30 DÍAS) ===\n";
$todosPagos = Pago::where('apartamento_id', $apartamento->id)
    ->where('created_at', '>=', now()->subDays(30))
    ->orderBy('created_at', 'desc')
    ->get();

if ($todosPagos->isEmpty()) {
    echo "❌ No se encontraron pagos recientes\n";
} else {
    foreach ($todosPagos as $pago) {
        $tipo = $pago->recibo_gasto_comun_id ? 'individual' : 'global';
        echo "📋 Pago ID: {$pago->id} | Tipo: {$tipo} | Monto: $" . number_format($pago->monto_pagado, 2) . " | Estado: {$pago->estado} | Fecha: {$pago->created_at}\n";
    }
}

// Buscar pagos de exactamente $100
echo "\n=== PAGOS DE EXACTAMENTE $100 ===\n";
$pagos100 = Pago::where('apartamento_id', $apartamento->id)
    ->where('monto_pagado', 100.00)
    ->orderBy('created_at', 'desc')
    ->get();

if ($pagos100->isEmpty()) {
    echo "❌ No se encontraron pagos de $100\n";
} else {
    foreach ($pagos100 as $pago) {
        echo "📋 Pago ID: {$pago->id}\n";
        echo "   💰 Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
        echo "   📅 Fecha: {$pago->fecha_pago}\n";
        echo "   📅 Creado: {$pago->created_at}\n";
        echo "   🔄 Estado: {$pago->estado}\n";
        $tipo = $pago->recibo_gasto_comun_id ? 'individual' : 'global';
        echo "   📝 Tipo: {$tipo}\n";
        echo "   📝 Método: {$pago->metodo_pago}\n";
        echo "   📄 Comprobante: {$pago->numero_comprobante}\n";
        
        if ($pago->recibo_gasto_comun_id) {
            $recibo = ReciboGastoComun::find($pago->recibo_gasto_comun_id);
            if ($recibo) {
                echo "   📋 Recibo asociado: {$recibo->numero_recibo}\n";
            }
        } else {
            echo "   📋 Recibo asociado: NINGUNO\n";
        }
        echo "   " . str_repeat("-", 50) . "\n";
    }
}

// Verificar el estado actual de los recibos del apartamento
echo "\n=== ESTADO ACTUAL DE RECIBOS ===\n";
$recibosConPagos = ReciboGastoComun::whereHas('pagos', function($query) use ($apartamento) {
    $query->where('apartamento_id', $apartamento->id)
          ->where('estado', '!=', 'rechazado');
})->with(['pagos' => function($query) use ($apartamento) {
    $query->where('apartamento_id', $apartamento->id)
          ->where('estado', '!=', 'rechazado');
}])->orderBy('fecha_vencimiento', 'asc')->get();

echo "Total de recibos con pagos activos: " . $recibosConPagos->count() . "\n\n";

foreach ($recibosConPagos as $recibo) {
    $totalPagado = $recibo->pagos->sum('monto_pagado');
    $saldoPendiente = $recibo->monto - $totalPagado;
    
    echo "📋 {$recibo->numero_recibo} | Monto: $" . number_format($recibo->monto, 2) . " | Pagado: $" . number_format($totalPagado, 2) . " | Pendiente: $" . number_format($saldoPendiente, 2) . "\n";
    
    // Mostrar detalles de pagos para este recibo
    foreach ($recibo->pagos as $pago) {
        $tipo = $pago->recibo_gasto_comun_id ? 'individual' : 'global';
        echo "   └─ Pago ID {$pago->id}: $" . number_format($pago->monto_pagado, 2) . " ({$tipo}) - {$pago->created_at}\n";
    }
}

echo "\n=== INVESTIGACIÓN COMPLETADA ===\n";