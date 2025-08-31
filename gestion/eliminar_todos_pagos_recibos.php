<?php

require_once __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\Pago;
use App\Models\ReciboGastoComun;
use Illuminate\Support\Facades\DB;

echo "=== ELIMINACIÓN COMPLETA DE PAGOS Y RECIBOS ===\n\n";

echo "⚠️  ADVERTENCIA: Este script eliminará TODOS los pagos y recibos del sistema.\n";
echo "Esta acción NO se puede deshacer.\n\n";

// Mostrar estadísticas actuales
echo "=== ESTADÍSTICAS ACTUALES ===\n";
$totalPagos = Pago::count();
$totalRecibos = ReciboGastoComun::count();
$totalApartamentos = Apartamento::count();

echo "Total de pagos: $totalPagos\n";
echo "Total de recibos: $totalRecibos\n";
echo "Total de apartamentos: $totalApartamentos\n\n";

// Distribución de pagos por estado
echo "Distribución de pagos por estado:\n";
$estadosPagos = Pago::select('estado', DB::raw('count(*) as total'))
    ->groupBy('estado')
    ->get();

foreach ($estadosPagos as $estado) {
    echo "  {$estado->estado}: {$estado->total}\n";
}

// Distribución de recibos por estado
echo "\nDistribución de recibos por estado:\n";
$estadosRecibos = ReciboGastoComun::select('estado', DB::raw('count(*) as total'))
    ->groupBy('estado')
    ->get();

foreach ($estadosRecibos as $estado) {
    echo "  {$estado->estado}: {$estado->total}\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "CONFIRMACIÓN REQUERIDA\n";
echo str_repeat("=", 50) . "\n\n";

echo "Para continuar con la eliminación, escriba exactamente: ELIMINAR TODO\n";
echo "Para cancelar, presione Enter o escriba cualquier otra cosa.\n\n";
echo "Su respuesta: ";

$confirmacion = trim(fgets(STDIN));

if ($confirmacion !== 'ELIMINAR TODO') {
    echo "\n❌ Operación cancelada por el usuario.\n";
    echo "No se realizaron cambios en el sistema.\n";
    exit;
}

echo "\n=== INICIANDO ELIMINACIÓN ===\n\n";

// Crear respaldo antes de eliminar
echo "1. Creando respaldo de datos...\n";
$timestamp = date('Y-m-d_H-i-s');
$backupFile = "backup_pagos_recibos_$timestamp.sql";

try {
    // Exportar datos de pagos
    $pagos = Pago::all();
    $recibos = ReciboGastoComun::all();
    
    $backupContent = "-- Respaldo de pagos y recibos - $timestamp\n\n";
    
    // Respaldo de pagos
    $backupContent .= "-- PAGOS\n";
    foreach ($pagos as $pago) {
        $backupContent .= "INSERT INTO pagos (id, apartamento_id, recibo_gasto_comun_id, monto, estado, fecha_pago, observaciones, created_at, updated_at) VALUES ";
        $backupContent .= "({$pago->id}, {$pago->apartamento_id}, {$pago->recibo_gasto_comun_id}, {$pago->monto}, '{$pago->estado}', ";
        $backupContent .= $pago->fecha_pago ? "'{$pago->fecha_pago}'" : 'NULL';
        $backupContent .= ", " . ($pago->observaciones ? "'" . addslashes($pago->observaciones) . "'" : 'NULL');
        $backupContent .= ", '{$pago->created_at}', '{$pago->updated_at}');\n";
    }
    
    // Respaldo de recibos
    $backupContent .= "\n-- RECIBOS\n";
    foreach ($recibos as $recibo) {
        $backupContent .= "INSERT INTO recibo_gasto_comuns (id, numero_recibo, monto, fecha_emision, fecha_vencimiento, estado, descripcion, created_at, updated_at) VALUES ";
        $backupContent .= "({$recibo->id}, '{$recibo->numero_recibo}', {$recibo->monto}, '{$recibo->fecha_emision}', '{$recibo->fecha_vencimiento}', '{$recibo->estado}', ";
        $backupContent .= ($recibo->descripcion ? "'" . addslashes($recibo->descripcion) . "'" : 'NULL');
        $backupContent .= ", '{$recibo->created_at}', '{$recibo->updated_at}');\n";
    }
    
    file_put_contents($backupFile, $backupContent);
    echo "   ✓ Respaldo creado: $backupFile\n\n";
    
} catch (Exception $e) {
    echo "   ❌ Error creando respaldo: " . $e->getMessage() . "\n";
    echo "   Operación cancelada por seguridad.\n";
    exit;
}

// Iniciar transacción
DB::beginTransaction();

try {
    echo "2. Eliminando todos los pagos...\n";
    $pagosEliminados = Pago::count();
    DB::table('pagos')->delete();
    echo "   ✓ Eliminados $pagosEliminados pagos\n\n";
    
    echo "3. Eliminando todos los recibos...\n";
    $recibosEliminados = ReciboGastoComun::count();
    DB::table('recibo_gasto_comuns')->delete();
    echo "   ✓ Eliminados $recibosEliminados recibos\n\n";
    
    echo "4. Actualizando estatus de apartamentos a 'solvente'...\n";
    $apartamentosActualizados = Apartamento::where('estatus_financiero', '!=', 'solvente')->count();
    Apartamento::query()->update(['estatus_financiero' => 'solvente']);
    echo "   ✓ Actualizados $apartamentosActualizados apartamentos a 'solvente'\n\n";
    
    // Confirmar transacción
    DB::commit();
    
    echo "✅ ELIMINACIÓN COMPLETADA EXITOSAMENTE\n\n";
    
    echo "=== RESUMEN DE LA OPERACIÓN ===\n";
    echo "Pagos eliminados: $pagosEliminados\n";
    echo "Recibos eliminados: $recibosEliminados\n";
    echo "Apartamentos actualizados: $apartamentosActualizados\n";
    echo "Respaldo creado: $backupFile\n\n";
    
    echo "=== ESTADO FINAL DEL SISTEMA ===\n";
    $finalPagos = Pago::count();
    $finalRecibos = ReciboGastoComun::count();
    $apartamentosSolventes = Apartamento::where('estatus_financiero', 'solvente')->count();
    
    echo "Pagos restantes: $finalPagos\n";
    echo "Recibos restantes: $finalRecibos\n";
    echo "Apartamentos solventes: $apartamentosSolventes de $totalApartamentos\n\n";
    
    echo "🎉 El sistema ha sido limpiado completamente.\n";
    echo "Todos los apartamentos están ahora en estado 'solvente'.\n";
    echo "Puede comenzar a cargar nuevos recibos desde cero.\n\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "❌ ERROR DURANTE LA ELIMINACIÓN: " . $e->getMessage() . "\n";
    echo "Se revirtieron todos los cambios.\n";
    echo "El respaldo se mantiene disponible: $backupFile\n";
}

echo "=== FIN DE LA OPERACIÓN ===\n";

?>