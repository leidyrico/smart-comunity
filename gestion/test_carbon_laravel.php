<?php

require_once 'vendor/autoload.php';

// Configurar Laravel para usar Carbon
use Illuminate\Support\Facades\Date;
use Carbon\Carbon;

echo "=== PRUEBA DE CARBON EN CONTEXTO LARAVEL ===\n\n";

// Probar exactamente lo que hace la vista
$pagosTest = [
    ['fecha' => '2025-09-03', 'monto' => 100],
    ['fecha' => null, 'monto' => 0],
    ['fecha' => '', 'monto' => 0],
    ['fecha' => '0000-00-00', 'monto' => 0]
];

foreach ($pagosTest as $i => $pago) {
    echo "Pago " . ($i + 1) . ": ";
    echo "fecha = " . ($pago['fecha'] === null ? 'NULL' : "'{$pago['fecha']}'") . ", ";
    echo "monto = {$pago['monto']}\n";
    
    // Simular exactamente la condición de la vista
    if ($pago['monto'] > 0 && !empty([$pago])) {
        echo "  ✅ Condición cumplida: monto > 0 y array no vacío\n";
        
        try {
            $fechaFormateada = \Carbon\Carbon::parse($pago['fecha'])->format('d/m/Y');
            echo "  📅 Fecha formateada: {$fechaFormateada}\n";
        } catch (Exception $e) {
            echo "  ❌ Error al formatear: " . $e->getMessage() . "\n";
        }
    } else {
        echo "  ❌ Condición NO cumplida: monto = {$pago['monto']}\n";
    }
    
    echo "  " . str_repeat("-", 40) . "\n";
}

echo "\n=== PRUEBA ESPECÍFICA CON NULL ===\n";

// Probar directamente con null
try {
    echo "Probando Carbon::parse(null)...\n";
    $resultado = Carbon::parse(null);
    echo "Resultado: " . $resultado->format('d/m/Y') . " (" . $resultado->format('Y-m-d H:i:s') . ")\n";
    echo "Es hoy?: " . ($resultado->isToday() ? 'SÍ' : 'NO') . "\n";
    echo "Es ayer?: " . ($resultado->isYesterday() ? 'SÍ' : 'NO') . "\n";
} catch (Exception $e) {
    echo "Error con null: " . $e->getMessage() . "\n";
}

echo "\n=== VERIFICACIÓN DE CONFIGURACIÓN DE CARBON ===\n";

// Verificar configuración de Carbon
echo "Zona horaria actual: " . Carbon::now()->getTimezone()->getName() . "\n";
echo "Fecha/hora actual: " . Carbon::now()->format('Y-m-d H:i:s') . "\n";
echo "Fecha actual formateada: " . Carbon::now()->format('d/m/Y') . "\n";
echo "Ayer: " . Carbon::yesterday()->format('d/m/Y') . "\n";
echo "Mañana: " . Carbon::tomorrow()->format('d/m/Y') . "\n";

echo "\n=== PRUEBA CON DIFERENTES VALORES FALSY ===\n";

$valoresFalsy = [null, false, 0, '', '0'];

foreach ($valoresFalsy as $i => $valor) {
    echo "Valor " . ($i + 1) . ": ";
    
    if ($valor === null) {
        echo "null";
    } elseif ($valor === false) {
        echo "false";
    } elseif ($valor === 0) {
        echo "0";
    } elseif ($valor === '') {
        echo "string vacío";
    } elseif ($valor === '0') {
        echo "'0'";
    }
    
    echo " -> ";
    
    try {
        $resultado = Carbon::parse($valor);
        echo $resultado->format('d/m/Y') . " (" . $resultado->format('Y-m-d') . ")";
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage();
    }
    
    echo "\n";
}

echo "\n=== PRUEBA COMPLETADA ===\n";