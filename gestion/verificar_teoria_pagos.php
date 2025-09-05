<?php

// Configuración de conexión a la base de datos
$host = 'localhost';
$dbname = 'smart_comunity';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== VERIFICACIÓN DE TEORÍA: PAGOS MIXTOS ===\n\n";
    
    // Buscar recibos que tengan tanto pagos con monto > 0 como pagos con monto = 0
    $sql = "
        SELECT 
            r.numero_recibo,
            a.numero as apartamento,
            COUNT(p.id) as total_pagos,
            SUM(CASE WHEN p.monto_pagado > 0 THEN 1 ELSE 0 END) as pagos_con_monto,
            SUM(CASE WHEN p.monto_pagado = 0 THEN 1 ELSE 0 END) as pagos_sin_monto,
            SUM(p.monto_pagado) as monto_total_pagado,
            GROUP_CONCAT(
                CONCAT('$', p.monto_pagado, ' (', 
                    CASE 
                        WHEN p.fecha_pago IS NULL THEN 'NULL'
                        ELSE p.fecha_pago 
                    END, 
                ')') 
                SEPARATOR ' | '
            ) as detalle_pagos
        FROM pagos p
        JOIN apartamentos a ON p.apartamento_id = a.id
        LEFT JOIN recibo_gasto_comuns r ON p.recibo_gasto_comun_id = r.id
        WHERE p.estado = 'confirmado'
        GROUP BY r.id, a.id
        HAVING pagos_con_monto > 0 AND pagos_sin_monto > 0
        ORDER BY r.numero_recibo, a.numero
        LIMIT 10
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $recibosMixtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Recibos con pagos mixtos (monto > 0 y monto = 0):\n";
    echo str_repeat("=", 80) . "\n";
    
    foreach ($recibosMixtos as $recibo) {
        echo "📋 Recibo: {$recibo['numero_recibo']} | Apartamento: {$recibo['apartamento']}\n";
        echo "   💰 Total pagado: $" . number_format($recibo['monto_total_pagado'], 2) . "\n";
        echo "   📊 Pagos: {$recibo['total_pagos']} total ({$recibo['pagos_con_monto']} con monto, {$recibo['pagos_sin_monto']} sin monto)\n";
        echo "   📝 Detalle: {$recibo['detalle_pagos']}\n";
        echo "   " . str_repeat("-", 70) . "\n";
    }
    
    if (empty($recibosMixtos)) {
        echo "❌ No se encontraron recibos con pagos mixtos\n";
    } else {
        echo "\n✅ Encontrados " . count($recibosMixtos) . " recibos con pagos mixtos\n";
    }
    
    // Verificar específicamente apartamentos que muestran $0.00 (04/09/2025)
    echo "\n=== ANÁLISIS ESPECÍFICO DE PAGOS CON FECHA NULL ===\n";
    
    $sql2 = "
        SELECT 
            a.numero as apartamento,
            r.numero_recibo,
            p.monto_pagado,
            p.fecha_pago,
            p.observaciones,
            p.created_at
        FROM pagos p
        JOIN apartamentos a ON p.apartamento_id = a.id
        LEFT JOIN recibo_gasto_comuns r ON p.recibo_gasto_comun_id = r.id
        WHERE p.fecha_pago IS NULL 
        AND p.estado = 'confirmado'
        AND p.apartamento_id IN (
            SELECT DISTINCT apartamento_id 
            FROM pagos 
            WHERE monto_pagado > 0 
            AND estado = 'confirmado'
        )
        ORDER BY a.numero, p.created_at DESC
        LIMIT 20
    ";
    
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute();
    $pagosNullConOtrosPagos = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Pagos con fecha NULL en apartamentos que también tienen pagos reales:\n";
    echo str_repeat("=", 80) . "\n";
    
    foreach ($pagosNullConOtrosPagos as $pago) {
        echo "🏠 Apartamento: {$pago['apartamento']} | Recibo: " . ($pago['numero_recibo'] ?: 'GLOBAL') . "\n";
        echo "   💰 Monto: $" . number_format($pago['monto_pagado'], 2) . "\n";
        echo "   📅 Fecha pago: " . ($pago['fecha_pago'] ?: 'NULL') . "\n";
        echo "   📝 Observaciones: {$pago['observaciones']}\n";
        echo "   📅 Creado: {$pago['created_at']}\n";
        echo "   " . str_repeat("-", 70) . "\n";
    }
    
    if (empty($pagosNullConOtrosPagos)) {
        echo "❌ No se encontraron pagos con fecha NULL en apartamentos con pagos reales\n";
    } else {
        echo "\n✅ Encontrados " . count($pagosNullConOtrosPagos) . " pagos con fecha NULL en apartamentos con pagos reales\n";
    }
    
    // Simular lo que ve el usuario
    echo "\n=== SIMULACIÓN DE LO QUE VE EL USUARIO ===\n";
    
    if (!empty($pagosNullConOtrosPagos)) {
        $primerPago = $pagosNullConOtrosPagos[0];
        echo "Ejemplo para Apartamento {$primerPago['apartamento']}:\n";
        echo "- Pago real: $21.23 (03/09/2025)\n";
        echo "- Pago automático: $0.00 (NULL) -> se muestra como $0.00 (04/09/2025)\n";
        echo "\nEsto explica por qué aparece $0.00 (04/09/2025) en todos los apartamentos\n";
        echo "que tienen pagos reales confirmados.\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
}

echo "\n=== VERIFICACIÓN COMPLETADA ===\n";