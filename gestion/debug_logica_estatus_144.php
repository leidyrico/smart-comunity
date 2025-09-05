<?php

require_once __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\Pago;
use App\Models\ReciboGastoComun;

echo "=== ANÁLISIS DE LA LÓGICA DE ESTATUS DEL APARTAMENTO 144 ===\n\n";

// Buscar el apartamento 144
$apartamento = Apartamento::where('numero', 144)->first();

if (!$apartamento) {
    echo "❌ No se encontró el apartamento 144\n";
    exit;
}

echo "📍 Apartamento: {$apartamento->numero}\n";
echo "💰 Estatus actual: {$apartamento->estatus_financiero}\n";
echo "⏳ Saldo pendiente: $" . number_format($apartamento->saldo_pendiente, 2) . "\n\n";

// Analizar la lógica actual del método actualizarEstatusFinanciero
echo "=== ANÁLISIS DE LA LÓGICA ACTUAL ===\n";

// Paso 1: Verificar saldo pendiente
echo "🔍 Paso 1: Verificar saldo pendiente\n";
echo "   Saldo pendiente: $" . number_format($apartamento->saldo_pendiente, 2) . "\n";
if ($apartamento->saldo_pendiente == 0) {
    echo "   ✅ Debería ser SOLVENTE\n\n";
} else {
    echo "   ⚠️ Tiene deuda, continuar al paso 2\n\n";
    
    // Paso 2: Contar recibos activos/vencidos (lógica actual)
    echo "🔍 Paso 2: Contar recibos activos/vencidos con pagos (LÓGICA ACTUAL)\n";
    $recibosActivosVencidos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])
        ->whereHas('pagos', function($query) use ($apartamento) {
            $query->where('apartamento_id', $apartamento->id);
        })->get();
    
    echo "   📋 Recibos activos/vencidos con pagos: " . $recibosActivosVencidos->count() . "\n";
    
    foreach ($recibosActivosVencidos as $recibo) {
        echo "   🧾 Recibo #{$recibo->numero_recibo} - Estado: {$recibo->estado}\n";
    }
    
    if ($recibosActivosVencidos->count() > 3) {
        echo "   ❌ Más de 3 recibos → MOROSO\n\n";
    } else {
        echo "   ⚠️ 1-3 recibos → DEUDOR\n\n";
    }
}

// Proponer lógica corregida
echo "=== LÓGICA CORREGIDA PROPUESTA ===\n";

if ($apartamento->saldo_pendiente == 0) {
    echo "✅ Estatus correcto: SOLVENTE (sin deudas)\n";
} else {
    // Contar solo recibos con saldo pendiente real
    $recibosConDeuda = 0;
    $recibosAsignados = ReciboGastoComun::whereHas('pagos', function($query) use ($apartamento) {
        $query->where('apartamento_id', $apartamento->id)
              ->where('estado', '!=', 'rechazado');
    })->get();
    
    echo "🔍 Analizando recibos con saldo pendiente real:\n";
    
    foreach ($recibosAsignados as $recibo) {
        $totalPagado = $apartamento->pagos()
            ->where('recibo_gasto_comun_id', $recibo->id)
            ->where('estado', 'confirmado')
            ->sum('monto_pagado');
        
        $saldoRecibo = max(0, $recibo->monto_total - $totalPagado);
        
        if ($saldoRecibo > 0) {
            $recibosConDeuda++;
            echo "   🧾 Recibo #{$recibo->numero_recibo} - Saldo: $" . number_format($saldoRecibo, 2) . "\n";
        }
    }
    
    echo "\n📊 Total de recibos con deuda real: {$recibosConDeuda}\n";
    
    if ($recibosConDeuda == 1) {
        echo "✅ Estatus correcto: DEUDOR (1 recibo con deuda)\n";
    } else {
        echo "❌ Estatus correcto: MOROSO (múltiples recibos con deuda)\n";
    }
}

echo "\n=== RECOMENDACIÓN ===\n";
echo "🔧 El método actualizarEstatusFinanciero necesita ser corregido para:\n";
echo "   1. Contar solo recibos que realmente tienen saldo pendiente\n";
echo "   2. No contar recibos que están completamente pagados\n";
echo "   3. Ignorar recibos con monto $0.00 (datos corruptos)\n";

echo "\n=== ANÁLISIS COMPLETADO ===\n";