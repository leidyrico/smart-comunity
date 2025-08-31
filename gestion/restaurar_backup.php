<?php

// Configuración de base de datos
$host = 'localhost';
$dbname = 'smart_comunity';
$username = 'root';
$password = '';

try {
    echo "=== RESTAURACIÓN DE BACKUP ===\n\n";
    
    // Buscar el archivo de backup más reciente
    $backupFiles = glob('backup_*.sql');
    if (empty($backupFiles)) {
        echo "No se encontraron archivos de backup.\n";
        exit;
    }
    
    // Ordenar por fecha de modificación (más reciente primero)
    usort($backupFiles, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    
    $latestBackup = $backupFiles[0];
    $backupSize = filesize($latestBackup);
    $backupDate = date('Y-m-d H:i:s', filemtime($latestBackup));
    
    echo "Archivo de backup más reciente: {$latestBackup}\n";
    echo "Tamaño: " . number_format($backupSize) . " bytes\n";
    echo "Fecha: {$backupDate}\n\n";
    
    // Leer el contenido del backup
    $sqlContent = file_get_contents($latestBackup);
    if ($sqlContent === false) {
        echo "Error: No se pudo leer el archivo de backup.\n";
        exit;
    }
    
    echo "Contenido del backup leído exitosamente.\n";
    echo "Tamaño del contenido: " . number_format(strlen($sqlContent)) . " caracteres\n\n";
    
    // Conectar a MySQL sin especificar base de datos
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "Conectado a MySQL exitosamente.\n";
    
    // Seleccionar la base de datos
    $pdo->exec("USE `$dbname`");
    echo "Base de datos '$dbname' seleccionada.\n\n";
    
    // Dividir el contenido en declaraciones SQL individuales
    $statements = array_filter(
        array_map('trim', explode(';', $sqlContent)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
        }
    );
    
    echo "Número de declaraciones SQL a ejecutar: " . count($statements) . "\n\n";
    
    // Desactivar verificaciones de claves foráneas temporalmente
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    echo "Verificaciones de claves foráneas desactivadas.\n";
    
    // Ejecutar cada declaración SQL
    $executed = 0;
    $errors = 0;
    
    foreach ($statements as $index => $statement) {
        try {
            $pdo->exec($statement);
            $executed++;
            
            // Mostrar progreso cada 10 declaraciones
            if (($executed % 10) == 0) {
                echo "Ejecutadas: $executed declaraciones...\n";
            }
        } catch (PDOException $e) {
            $errors++;
            echo "Error en declaración " . ($index + 1) . ": " . $e->getMessage() . "\n";
            
            // Mostrar la declaración que falló (primeros 100 caracteres)
            $preview = substr($statement, 0, 100);
            echo "Declaración: $preview...\n\n";
        }
    }
    
    // Reactivar verificaciones de claves foráneas
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "Verificaciones de claves foráneas reactivadas.\n\n";
    
    echo "=== RESUMEN DE RESTAURACIÓN ===\n";
    echo "Declaraciones ejecutadas exitosamente: $executed\n";
    echo "Errores encontrados: $errors\n\n";
    
    // Verificar el estado después de la restauración
    echo "=== VERIFICACIÓN POST-RESTAURACIÓN ===\n";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM apartamentos");
    $stmt->execute();
    $apartamentos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "Apartamentos restaurados: $apartamentos\n";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM recibo_gasto_comuns");
    $stmt->execute();
    $recibos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "Recibos restaurados: $recibos\n";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM pagos");
    $stmt->execute();
    $pagos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "Pagos restaurados: $pagos\n\n";
    
    if ($apartamentos > 0 && $recibos > 0 && $pagos > 0) {
        echo "✓ Restauración completada exitosamente.\n";
        echo "Ahora puedes ejecutar el script de verificación del apartamento 13.\n";
    } else {
        echo "⚠ La restauración puede no haber sido completamente exitosa.\n";
        echo "Verifica los errores anteriores.\n";
    }
    
} catch (PDOException $e) {
    echo "Error de base de datos: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error general: " . $e->getMessage() . "\n";
}

?>