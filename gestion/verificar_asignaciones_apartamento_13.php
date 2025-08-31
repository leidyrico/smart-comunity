<?php

// Configuración de base de datos
$host = 'localhost';
$dbname = 'smart_comunity';
$username = 'root';
$password = '';

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    
    echo "=== VERIFICACIÓN DE ASIGNACIONES APARTAMENTO 13 ===\n\n";
    
    // Verificar conexión a la base de datos
    echo "Verificando conexión a la base de datos...\n";
    $stmt = $pdo->query("SELECT DATABASE() as db_name");
    $dbInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Base de datos conectada: {$dbInfo['db_name']}\n\n";
    
    // Verificar si existen tablas
    echo "Verificando tablas disponibles:\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "- {$table}\n";
    }
    echo "\n";
    
    // Contar registros en todas las tablas principales
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM apartamentos");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total de apartamentos: {$count['total']}\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM recibo_gasto_comuns");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total de recibos: {$count['total']}\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pagos");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total de pagos: {$count['total']}\n\n";
    
    // Si no hay apartamentos, verificar si hay un backup reciente
    if ($count['total'] == 0) {
        echo "⚠ PROBLEMA: No hay datos en las tablas principales.\n";
        echo "Esto sugiere que los datos se perdieron o estamos en una base de datos incorrecta.\n";
        echo "\nVerificando archivos de backup disponibles...\n";
        
        $backupFiles = glob('backup_*.sql');
        if (!empty($backupFiles)) {
            echo "Archivos de backup encontrados:\n";
            foreach ($backupFiles as $file) {
                $size = filesize($file);
                $date = date('Y-m-d H:i:s', filemtime($file));
                echo "- {$file} (Tamaño: " . number_format($size) . " bytes, Fecha: {$date})\n";
            }
            echo "\nRecomendación: Restaurar desde el backup más reciente.\n";
        } else {
            echo "No se encontraron archivos de backup en el directorio actual.\n";
        }
        
        exit;
    }
    
    // Primero verificar qué apartamentos existen
    echo "Apartamentos disponibles:\n";
    $stmt = $pdo->prepare("SELECT id, numero, propietario FROM apartamentos ORDER BY numero LIMIT 10");
    $stmt->execute();
    $apartamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($apartamentos)) {
        echo "No hay apartamentos en la base de datos.\n";
    } else {
        foreach ($apartamentos as $apt) {
            echo "- ID: {$apt['id']}, Número: {$apt['numero']}, Propietario: {$apt['propietario']}\n";
        }
    }
    echo "\n";
    
    // Obtener información del apartamento 13
    $stmt = $pdo->prepare("
        SELECT id, numero, propietario 
        FROM apartamentos 
        WHERE numero = '13'
    ");
    $stmt->execute();
    $apartamento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$apartamento) {
        echo "No se encontró el apartamento 13\n";
        echo "Intentando buscar por ID 91...\n";
        
        $stmt = $pdo->prepare("
            SELECT id, numero, propietario 
            FROM apartamentos 
            WHERE id = 91
        ");
        $stmt->execute();
        $apartamento = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$apartamento) {
            echo "Tampoco se encontró el apartamento con ID 91\n";
            exit;
        }
    }
    
    echo "Apartamento: {$apartamento['numero']} - {$apartamento['propietario']}\n";
    echo "ID: {$apartamento['id']}\n\n";
    
    // Contar todos los recibos asignados
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM pagos p
        WHERE p.apartamento_id = ?
    ");
    $stmt->execute([$apartamento['id']]);
    $totalRecibos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "Total de recibos asignados: {$totalRecibos}\n\n";
    
    // Contar recibos con asignación manual
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM pagos p
        WHERE p.apartamento_id = ?
        AND p.observaciones LIKE '%Asignación manual%'
    ");
    $stmt->execute([$apartamento['id']]);
    $asignacionesManual = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "Recibos con asignación MANUAL: {$asignacionesManual}\n";
    
    // Contar recibos con asignación automática
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM pagos p
        WHERE p.apartamento_id = ?
        AND (p.observaciones LIKE '%automáticamente%' OR p.observaciones LIKE '%Recibo asignado automáticamente%')
    ");
    $stmt->execute([$apartamento['id']]);
    $asignacionesAuto = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "Recibos con asignación AUTOMÁTICA: {$asignacionesAuto}\n";
    
    // Contar otros tipos
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM pagos p
        WHERE p.apartamento_id = ?
        AND p.observaciones NOT LIKE '%Asignación manual%'
        AND p.observaciones NOT LIKE '%automáticamente%'
        AND p.observaciones NOT LIKE '%Recibo asignado automáticamente%'
    ");
    $stmt->execute([$apartamento['id']]);
    $otros = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "Otros tipos de asignación: {$otros}\n\n";
    
    // Mostrar detalles de las asignaciones manuales
    echo "=== DETALLES DE ASIGNACIONES MANUALES ===\n";
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            r.numero_recibo,
            r.fecha_emision,
            r.fecha_vencimiento,
            r.total_recibo,
            p.monto_pagado,
            p.estado,
            p.observaciones,
            p.created_at
        FROM pagos p
        JOIN recibo_gasto_comuns r ON p.recibo_gasto_comun_id = r.id
        WHERE p.apartamento_id = ?
        AND p.observaciones LIKE '%Asignación manual%'
        ORDER BY r.fecha_emision DESC
    ");
    $stmt->execute([$apartamento['id']]);
    $asignacionesManuales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($asignacionesManuales as $asignacion) {
        echo "- {$asignacion['numero_recibo']} | ";
        echo "Emisión: {$asignacion['fecha_emision']} | ";
        echo "Vencimiento: {$asignacion['fecha_vencimiento']} | ";
        echo "Total: $" . number_format($asignacion['total_recibo'], 2) . " | ";
        echo "Pagado: $" . number_format($asignacion['monto_pagado'], 2) . " | ";
        echo "Estado: {$asignacion['estado']} | ";
        echo "Creado: {$asignacion['created_at']}\n";
    }
    
    echo "\n=== DETALLES DE ASIGNACIONES AUTOMÁTICAS ===\n";
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            r.numero_recibo,
            r.fecha_emision,
            r.fecha_vencimiento,
            r.total_recibo,
            p.monto_pagado,
            p.estado,
            p.observaciones,
            p.created_at
        FROM pagos p
        JOIN recibo_gasto_comuns r ON p.recibo_gasto_comun_id = r.id
        WHERE p.apartamento_id = ?
        AND (p.observaciones LIKE '%automáticamente%' OR p.observaciones LIKE '%Recibo asignado automáticamente%')
        ORDER BY r.fecha_emision DESC
        LIMIT 10
    ");
    $stmt->execute([$apartamento['id']]);
    $asignacionesAuto = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($asignacionesAuto as $asignacion) {
        echo "- {$asignacion['numero_recibo']} | ";
        echo "Emisión: {$asignacion['fecha_emision']} | ";
        echo "Vencimiento: {$asignacion['fecha_vencimiento']} | ";
        echo "Total: $" . number_format($asignacion['total_recibo'], 2) . " | ";
        echo "Pagado: $" . number_format($asignacion['monto_pagado'], 2) . " | ";
        echo "Estado: {$asignacion['estado']} | ";
        echo "Creado: {$asignacion['created_at']}\n";
    }
    
    echo "\n=== CONCLUSIÓN ===\n";
    echo "La página de deudas solo muestra recibos con 'Asignación manual'.\n";
    echo "Por eso muestra {$asignacionesManual} recibos en lugar de {$totalRecibos}.\n";
    
    if ($asignacionesManual == 20) {
        echo "✓ El número de asignaciones manuales (20) coincide con lo esperado.\n";
    } else {
        echo "⚠ El número de asignaciones manuales ({$asignacionesManual}) no coincide con lo esperado (20).\n";
    }
    
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage() . "\n";
}

?>