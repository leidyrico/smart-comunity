<?php

require_once 'vendor/autoload.php';

use Carbon\Carbon;

echo "=== PRUEBA DE CARBON::PARSE CON NULL ===\n\n";

// Probar diferentes valores que podrían estar causando el problema
$valores = [
    null,
    '',
    '0000-00-00',
    '0000-00-00 00:00:00',
    false,
    0
];

foreach ($valores as $i => $valor) {
    echo "Prueba " . ($i + 1) . ": ";
    
    if ($valor === null) {
        echo "null";
    } elseif ($valor === '') {
        echo "string vacío ''";
    } elseif ($valor === false) {
        echo "false";
    } elseif ($valor === 0) {
        echo "0";
    } else {
        echo "'$valor'";
    }
    
    echo " -> ";
    
    try {
        if ($valor === null || $valor === '') {
            echo "No se puede parsear (null/vacío)\n";
        } else {
            $fecha = Carbon::parse($valor);
            echo $fecha->format('d/m/Y') . " (" . $fecha->format('Y-m-d') . ")\n";
        }
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

echo "\n=== PRUEBA DE COMPORTAMIENTO EN BLADE ===\n";

// Simular lo que hace la vista
$pagos = [
    ['fecha' => '2025-09-03'],
    ['fecha' => null],
    ['fecha' => ''],
    ['fecha' => '0000-00-00']
];

foreach ($pagos as $i => $pago) {
    echo "Pago " . ($i + 1) . ": fecha = ";
    
    if ($pago['fecha'] === null) {
        echo "null";
    } elseif ($pago['fecha'] === '') {
        echo "string vacío";
    } else {
        echo "'{$pago['fecha']}'";
    }
    
    echo " -> ";
    
    // Simular la condición de la vista
    if (!empty($pago['fecha']) && $pago['fecha'] !== null) {
        try {
            $fecha = Carbon::parse($pago['fecha']);
            echo $fecha->format('d/m/Y');
        } catch (Exception $e) {
            echo "ERROR: " . $e->getMessage();
        }
    } else {
        echo "No se muestra fecha (null/vacío)";
    }
    
    echo "\n";
}

echo "\n=== VERIFICACIÓN DE FECHA ACTUAL ===\n";
echo "Fecha actual: " . Carbon::now()->format('d/m/Y') . " (" . Carbon::now()->format('Y-m-d') . ")\n";
echo "Fecha actual + 1 año: " . Carbon::now()->addYear()->format('d/m/Y') . " (" . Carbon::now()->addYear()->format('Y-m-d') . ")\n";

echo "\n=== PRUEBA COMPLETADA ===\n";