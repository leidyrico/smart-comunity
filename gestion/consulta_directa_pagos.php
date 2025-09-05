<?php

// Configuración de conexión a la base de datos
$host = 'localhost';
$dbname = 'smart_comunity';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== INVESTIGACIÓN DE PAGOS CON FECHA 04/09/2025 ===\n\n";
    
    // Buscar pagos con fecha específica
    $stmt = $pdo->prepare("SELECT * FROM pagos WHERE fecha_pago = '2025-09-04'");
    $stmt->execute();
    $pagosFecha = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Pagos con fecha 2025-09-04: " . count($pagosFecha) . "\n\n";
    
    if (count($pagosFecha) > 0) {
        foreach (array_slice($pagosFecha, 0, 10) as $pago) {
            // Obtener información del apartamento
            $stmtApt = $pdo->prepare("SELECT numero, propietario FROM apartamentos WHERE id = ?");
            $stmtApt->execute([$pago['apartamento_id']]);
            $apartamento = $stmtApt->fetch(PDO::FETCH_ASSOC);
            
            // Obtener información del recibo
            $recibo = null;
            if ($pago['recibo_gasto_comun_id']) {
                $stmtRec = $pdo->prepare("SELECT numero_recibo FROM recibo_gasto_comuns WHERE id = ?");
                $stmtRec->execute([$pago['recibo_gasto_comun_id']]);
                $recibo = $stmtRec->fetch(PDO::FETCH_ASSOC);
            }
            
            echo "📋 Pago ID: {$pago['id']}\n";
            echo "   🏠 Apartamento: " . ($apartamento ? $apartamento['numero'] : 'N/A') . "\n";
            echo "   👤 Propietario: " . ($apartamento ? $apartamento['propietario'] : 'N/A') . "\n";
            echo "   📄 Recibo: " . ($recibo ? $recibo['numero_recibo'] : 'GLOBAL') . "\n";
            echo "   💰 Monto: $" . number_format($pago['monto_pagado'], 2) . "\n";
            echo "   📅 Fecha pago: {$pago['fecha_pago']}\n";
            echo "   🔄 Estado: {$pago['estado']}\n";
            echo "   📝 Observaciones: {$pago['observaciones']}\n";
            echo "   📅 Creado: {$pago['created_at']}\n";
            echo "   " . str_repeat("-", 50) . "\n";
        }
    }
    
    // Buscar pagos con monto 0 y fecha no nula
    echo "\n=== PAGOS CON MONTO $0.00 Y FECHA NO NULA ===\n";
    
    $stmt = $pdo->prepare("SELECT fecha_pago, COUNT(*) as cantidad FROM pagos WHERE monto_pagado = 0 AND fecha_pago IS NOT NULL GROUP BY fecha_pago ORDER BY cantidad DESC LIMIT 10");
    $stmt->execute();
    $fechasComunes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Fechas más comunes en pagos $0.00:\n";
    foreach ($fechasComunes as $fecha) {
        echo "📅 {$fecha['fecha_pago']}: {$fecha['cantidad']} pagos\n";
    }
    
    // Verificar si existe la fecha 04/09/2025 en algún formato
    echo "\n=== VERIFICACIÓN ESPECÍFICA FECHA 04/09/2025 ===\n";
    
    $fechasBuscar = ['2025-09-04', '2025-04-09'];
    $encontradoAlguno = false;
    
    foreach ($fechasBuscar as $fechaBuscar) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as cantidad FROM pagos WHERE fecha_pago = ?");
        $stmt->execute([$fechaBuscar]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado['cantidad'] > 0) {
            echo "✅ Formato {$fechaBuscar}: {$resultado['cantidad']} pagos\n";
            $encontradoAlguno = true;
        }
    }
    
    if (!$encontradoAlguno) {
        echo "❌ No se encontraron pagos con fecha 04/09/2025 en ningún formato\n";
    }
    
    // Buscar todos los pagos con monto 0 para entender el patrón
    echo "\n=== ANÁLISIS DE PAGOS CON MONTO $0.00 ===\n";
    
    $stmt = $pdo->prepare("SELECT observaciones, COUNT(*) as cantidad FROM pagos WHERE monto_pagado = 0 GROUP BY observaciones ORDER BY cantidad DESC LIMIT 10");
    $stmt->execute();
    $observaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Observaciones más comunes en pagos $0.00:\n";
    foreach ($observaciones as $obs) {
        echo "📝 {$obs['observaciones']}: {$obs['cantidad']} pagos\n";
    }
    
    // Buscar pagos recientes con monto 0
    echo "\n=== PAGOS RECIENTES CON MONTO $0.00 ===\n";
    
    $stmt = $pdo->prepare("SELECT p.*, a.numero as apartamento_numero, r.numero_recibo FROM pagos p LEFT JOIN apartamentos a ON p.apartamento_id = a.id LEFT JOIN recibo_gasto_comuns r ON p.recibo_gasto_comun_id = r.id WHERE p.monto_pagado = 0 ORDER BY p.created_at DESC LIMIT 5");
    $stmt->execute();
    $pagosRecientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($pagosRecientes as $pago) {
        echo "📋 ID: {$pago['id']} | Apt: {$pago['apartamento_numero']} | Recibo: " . ($pago['numero_recibo'] ?: 'GLOBAL') . " | Fecha: {$pago['fecha_pago']} | Creado: {$pago['created_at']}\n";
        echo "   📝 {$pago['observaciones']}\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
}

echo "\n=== INVESTIGACIÓN COMPLETADA ===\n";