<?php

// Configuración de conexión a la base de datos
$host = 'localhost';
$dbname = 'smart_comunity';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== INVESTIGACIÓN DE FECHAS NULL EN PAGOS ===\n\n";
    
    // Buscar pagos con fecha_pago NULL
    $stmt = $pdo->prepare("SELECT p.*, a.numero as apartamento_numero, r.numero_recibo FROM pagos p LEFT JOIN apartamentos a ON p.apartamento_id = a.id LEFT JOIN recibo_gasto_comuns r ON p.recibo_gasto_comun_id = r.id WHERE p.fecha_pago IS NULL ORDER BY p.created_at DESC LIMIT 10");
    $stmt->execute();
    $pagosNull = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Pagos con fecha_pago NULL (últimos 10):\n";
    foreach ($pagosNull as $pago) {
        echo "📋 ID: {$pago['id']} | Apt: {$pago['apartamento_numero']} | Recibo: " . ($pago['numero_recibo'] ?: 'GLOBAL') . " | Monto: $" . number_format($pago['monto_pagado'], 2) . "\n";
        echo "   📝 {$pago['observaciones']}\n";
        echo "   📅 Creado: {$pago['created_at']}\n";
        echo "   " . str_repeat("-", 50) . "\n";
    }
    
    // Contar total de pagos con fecha_pago NULL
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM pagos WHERE fecha_pago IS NULL");
    $stmt->execute();
    $totalNull = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\nTotal de pagos con fecha_pago NULL: {$totalNull['total']}\n";
    
    // Buscar pagos con fecha_pago no NULL para comparar
    $stmt = $pdo->prepare("SELECT p.*, a.numero as apartamento_numero, r.numero_recibo FROM pagos p LEFT JOIN apartamentos a ON p.apartamento_id = a.id LEFT JOIN recibo_gasto_comuns r ON p.recibo_gasto_comun_id = r.id WHERE p.fecha_pago IS NOT NULL ORDER BY p.created_at DESC LIMIT 5");
    $stmt->execute();
    $pagosConFecha = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n=== PAGOS CON FECHA_PAGO NO NULL (últimos 5) ===\n";
    foreach ($pagosConFecha as $pago) {
        echo "📋 ID: {$pago['id']} | Apt: {$pago['apartamento_numero']} | Recibo: " . ($pago['numero_recibo'] ?: 'GLOBAL') . " | Monto: $" . number_format($pago['monto_pagado'], 2) . "\n";
        echo "   📅 Fecha pago: {$pago['fecha_pago']}\n";
        echo "   📝 {$pago['observaciones']}\n";
        echo "   📅 Creado: {$pago['created_at']}\n";
        echo "   " . str_repeat("-", 50) . "\n";
    }
    
    // Verificar si hay algún patrón en las fechas
    echo "\n=== ANÁLISIS DE FECHAS ESPECÍFICAS ===\n";
    
    // Buscar fechas que podrían estar relacionadas con 04/09/2025
    $fechasRelacionadas = [
        '2025-09-04',
        '2025-04-09',
        '2024-09-04',
        '2024-04-09'
    ];
    
    foreach ($fechasRelacionadas as $fecha) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as cantidad FROM pagos WHERE fecha_pago = ?");
        $stmt->execute([$fecha]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado['cantidad'] > 0) {
            echo "📅 Fecha {$fecha}: {$resultado['cantidad']} pagos\n";
        }
    }
    
    // Verificar si hay alguna configuración o valor por defecto que genere 04/09/2025
    echo "\n=== VERIFICACIÓN DE CONFIGURACIONES ===\n";
    
    // Simular lo que hace Carbon::parse() con null
    echo "Simulando Carbon::parse() con valores null:\n";
    
    // En PHP, vamos a simular lo que podría estar pasando
    $fechaNull = null;
    $fechaVacia = '';
    $fecha0 = '0000-00-00';
    
    echo "- null: " . ($fechaNull ?: 'NULL') . "\n";
    echo "- string vacío: '" . $fechaVacia . "'\n";
    echo "- fecha cero: '" . $fecha0 . "'\n";
    
    // Verificar si hay algún valor por defecto en la base de datos
    $stmt = $pdo->prepare("DESCRIBE pagos fecha_pago");
    $stmt->execute();
    $columnaInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\nInformación de la columna fecha_pago:\n";
    echo "- Tipo: {$columnaInfo['Type']}\n";
    echo "- Null: {$columnaInfo['Null']}\n";
    echo "- Default: " . ($columnaInfo['Default'] ?: 'NULL') . "\n";
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
}

echo "\n=== INVESTIGACIÓN COMPLETADA ===\n";