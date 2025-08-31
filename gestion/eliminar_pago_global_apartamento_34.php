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

echo "=== ELIMINACIÓN DE PAGO GLOBAL APARTAMENTO 34 ===\n\n";

// Buscar el apartamento 34
$apartamento34 = Apartamento::where('numero', 34)->first();

if (!$apartamento34) {
    echo "❌ No se encontró el apartamento 34\n";
    exit;
}

echo "✅ Apartamento: {$apartamento34->numero} - {$apartamento34->propietario}\n\n";

// Buscar el pago global específico
$pagoGlobal = Pago::where('apartamento_id', $apartamento34->id)
    ->where('numero_comprobante', '52417832949')
    ->where('monto_pagado', 50.00)
    ->whereNull('recibo_gasto_comun_id')
    ->where('estado', 'confirmado')
    ->first();

if (!$pagoGlobal) {
    echo "❌ No se encontró el pago global de $50 con comprobante 52417832949 para el apartamento 34\n";
    
    // Buscar pagos similares para diagnóstico
    echo "\n🔍 Buscando pagos similares...\n";
    
    $pagosSimilares = Pago::where('apartamento_id', $apartamento34->id)
        ->where('numero_comprobante', '52417832949')
        ->get();
    
    if ($pagosSimilares->isNotEmpty()) {
        echo "📋 Pagos encontrados con comprobante 52417832949:\n";
        foreach ($pagosSimilares as $pago) {
            $recibo = $pago->recibo_gasto_comun_id ? ReciboGastoComun::find($pago->recibo_gasto_comun_id) : null;
            echo "   💳 ID {$pago->id}: $" . number_format($pago->monto_pagado, 2) . " - " . ($recibo ? $recibo->numero_recibo : 'GLOBAL') . " - {$pago->estado}\n";
        }
    } else {
        echo "❌ No se encontraron pagos con comprobante 52417832949\n";
    }
    
    exit;
}

echo "📋 Pago global encontrado:\n";
echo "   💳 ID: {$pagoGlobal->id}\n";
echo "   💰 Monto: $" . number_format($pagoGlobal->monto_pagado, 2) . "\n";
echo "   📅 Fecha: {$pagoGlobal->fecha_pago}\n";
echo "   📄 Comprobante: {$pagoGlobal->numero_comprobante}\n";
echo "   🔄 Estado: {$pagoGlobal->estado}\n";
echo "   📝 Observaciones: " . substr($pagoGlobal->observaciones, 0, 100) . "...\n";
echo "   " . str_repeat("-", 50) . "\n";

echo "\n=== INICIANDO ELIMINACIÓN ===\n";

try {
    DB::beginTransaction();
    
    // Crear backup antes de eliminar
    echo "📋 Creando backup...\n";
    $backupData = [
        'id' => $pagoGlobal->id,
        'apartamento_id' => $pagoGlobal->apartamento_id,
        'apartamento_numero' => $apartamento34->numero,
        'apartamento_propietario' => $apartamento34->propietario,
        'recibo_gasto_comun_id' => $pagoGlobal->recibo_gasto_comun_id,
        'monto_pagado' => $pagoGlobal->monto_pagado,
        'fecha_pago' => $pagoGlobal->fecha_pago,
        'numero_comprobante' => $pagoGlobal->numero_comprobante,
        'estado' => $pagoGlobal->estado,
        'observaciones' => $pagoGlobal->observaciones,
        'created_at' => $pagoGlobal->created_at,
        'updated_at' => $pagoGlobal->updated_at,
        'eliminado_en' => now()
    ];
    
    // Guardar backup
    $backupFile = 'backup_pago_global_apartamento_34_' . date('Y-m-d_H-i-s') . '.json';
    file_put_contents($backupFile, json_encode($backupData, JSON_PRETTY_PRINT));
    echo "✅ Backup guardado en: {$backupFile}\n\n";
    
    // Eliminar el pago
    echo "🗑️  Eliminando pago global ID {$pagoGlobal->id}...\n";
    $pagoGlobal->delete();
    
    DB::commit();
    
    echo "\n🎉 ELIMINACIÓN COMPLETADA EXITOSAMENTE\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "❌ Error durante la eliminación: " . $e->getMessage() . "\n";
    echo "🔄 Transacción revertida\n";
    exit;
}

// Verificación final
echo "\n=== VERIFICACIÓN FINAL ===\n";

// Verificar que el pago fue eliminado
$pagoEliminado = Pago::where('apartamento_id', $apartamento34->id)
    ->where('numero_comprobante', '52417832949')
    ->where('monto_pagado', 50.00)
    ->whereNull('recibo_gasto_comun_id')
    ->first();

if (!$pagoEliminado) {
    echo "✅ El pago global de $50 ha sido eliminado correctamente\n";
} else {
    echo "⚠️  Advertencia: El pago aún existe en la base de datos\n";
}

// Verificar crédito global actual
$creditoGlobalActual = Pago::where('apartamento_id', $apartamento34->id)
    ->whereNull('recibo_gasto_comun_id')
    ->where('estado', 'confirmado')
    ->sum('monto_pagado');

echo "💰 Crédito global actual del apartamento 34: $" . number_format($creditoGlobalActual, 2) . "\n";

// Verificar otros pagos con el mismo comprobante
$otrosPagosComprobante = Pago::where('apartamento_id', $apartamento34->id)
    ->where('numero_comprobante', '52417832949')
    ->count();

echo "📊 Otros pagos restantes con comprobante 52417832949: {$otrosPagosComprobante}\n";

// Mostrar resumen de pagos del apartamento 34
echo "\n=== RESUMEN DE PAGOS APARTAMENTO 34 ===\n";

$totalPagosConfirmados = Pago::where('apartamento_id', $apartamento34->id)
    ->where('estado', 'confirmado')
    ->sum('monto_pagado');

$totalPagosGlobales = Pago::where('apartamento_id', $apartamento34->id)
    ->whereNull('recibo_gasto_comun_id')
    ->where('estado', 'confirmado')
    ->sum('monto_pagado');

$totalPagosEspecificos = Pago::where('apartamento_id', $apartamento34->id)
    ->whereNotNull('recibo_gasto_comun_id')
    ->where('estado', 'confirmado')
    ->sum('monto_pagado');

echo "💳 Total pagos confirmados: $" . number_format($totalPagosConfirmados, 2) . "\n";
echo "🌐 Total pagos globales: $" . number_format($totalPagosGlobales, 2) . "\n";
echo "📋 Total pagos específicos: $" . number_format($totalPagosEspecificos, 2) . "\n";

echo "\n=== ELIMINACIÓN COMPLETADA ===\n";
echo "✅ Pago global eliminado: $50.00\n";
echo "📄 Comprobante eliminado: 52417832949\n";
echo "📁 Backup disponible: {$backupFile}\n";