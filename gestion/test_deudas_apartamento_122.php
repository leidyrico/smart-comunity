<?php

require_once __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Http\Controllers\DeudaController;
use Illuminate\Http\Request;

echo "=== PRUEBA DE VISTA DE DEUDAS - APARTAMENTO 122 ===\n\n";

// Simular la request con filtro para apartamento 122
$request = new Request([
    'numero_apartamento' => '122',
    'nombre_propietario' => '',
    'estado_deuda' => '',
    'numero_recibo' => ''
]);

// Crear instancia del controlador
$controller = new DeudaController();

echo "🔍 Simulando la vista de deudas con filtro apartamento 122...\n\n";

// Obtener el apartamento antes de la vista
$apartamento = Apartamento::where('numero', '122')->first();
if ($apartamento) {
    echo "📋 Apartamento encontrado: {$apartamento->numero} - {$apartamento->propietario}\n";
    echo "💰 Saldo pendiente ANTES de la vista: $" . number_format($apartamento->saldo_pendiente, 2) . "\n";
    echo "🏷️  Estatus financiero ANTES: {$apartamento->estatus_financiero}\n\n";
    
    // Simular el método index del DeudaController
    echo "🔄 Ejecutando lógica del DeudaController...\n";
    
    // Actualizar estatus financiero (como hace ahora el controlador)
    $apartamento->actualizarEstatusFinanciero();
    
    // Refrescar el modelo para obtener los datos actualizados
    $apartamento->refresh();
    
    echo "💰 Saldo pendiente DESPUÉS de actualizar: $" . number_format($apartamento->saldo_pendiente, 2) . "\n";
    echo "🏷️  Estatus financiero DESPUÉS: {$apartamento->estatus_financiero}\n\n";
    
    // Verificar recibos asignados
    $recibosAsignados = $apartamento->pagos
        ->where('estado', '!=', 'rechazado')
        ->pluck('recibo_gasto_comun_id')
        ->unique();
    
    echo "📋 Recibos asignados al apartamento: {$recibosAsignados->count()}\n";
    
    foreach ($recibosAsignados as $reciboId) {
        $recibo = ReciboGastoComun::find($reciboId);
        if ($recibo) {
            $pagosTotales = $apartamento->pagos
                ->where('recibo_gasto_comun_id', $recibo->id)
                ->where('estado', 'confirmado')
                ->sum('monto_pagado');
            
            $saldoPendiente = $recibo->total_recibo - $pagosTotales;
            
            echo "   📋 {$recibo->numero_recibo}:\n";
            echo "      💰 Total: $" . number_format($recibo->total_recibo, 2) . "\n";
            echo "      ✅ Pagado: $" . number_format($pagosTotales, 2) . "\n";
            echo "      ⚠️  Pendiente: $" . number_format($saldoPendiente, 2) . "\n";
            echo "      📅 Estado: {$recibo->estado}\n\n";
        }
    }
    
} else {
    echo "❌ Apartamento 122 no encontrado\n";
}

echo "=== FIN DE LA PRUEBA ===\n";