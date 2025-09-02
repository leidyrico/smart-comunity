<?php

// Script simple para verificar recibos del apartamento 72

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=smart_comunity;charset=utf8mb4",
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "=== VERIFICACIÓN CONEXIÓN Y DATOS ===\n\n";
    
    // Verificar conexión
    echo "✓ Conexión a base de datos exitosa\n\n";
    
    // Verificar tablas existentes
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tablas en la base de datos:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    
    // Verificar si existe la tabla apartamentos
    if (in_array('apartamentos', $tables)) {
        echo "\n✓ Tabla 'apartamentos' encontrada\n";
        
        // Contar apartamentos
        $stmt = $pdo->query("SELECT COUNT(*) FROM apartamentos");
        $count = $stmt->fetchColumn();
        echo "Total de apartamentos: $count\n\n";
        
        if ($count > 0) {
            // Mostrar algunos apartamentos
            $stmt = $pdo->query("SELECT id, numero, propietario FROM apartamentos LIMIT 10");
            $apartamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "Primeros 10 apartamentos:\n";
            foreach ($apartamentos as $apt) {
                echo "- ID: {$apt['id']}, Número: {$apt['numero']}, Propietario: {$apt['propietario']}\n";
            }
            
            // Buscar específicamente el apartamento 122 (que sabemos que existe)
            $stmt = $pdo->prepare("SELECT * FROM apartamentos WHERE numero = '122'");
            $stmt->execute();
            $apt122 = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($apt122) {
                echo "\n✓ Apartamento 122 encontrado:\n";
                echo "- ID: {$apt122['id']}\n";
                echo "- Propietario: {$apt122['propietario']}\n";
            }
            
            // Buscar apartamento con número que contenga '72'
            $stmt = $pdo->prepare("SELECT * FROM apartamentos WHERE numero LIKE '%72%'");
            $stmt->execute();
            $apartamentos72 = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "\nApartamentos que contienen '72':\n";
            foreach ($apartamentos72 as $apt) {
                echo "- ID: {$apt['id']}, Número: {$apt['numero']}, Propietario: {$apt['propietario']}\n";
            }
        }
    } else {
        echo "\n✗ Tabla 'apartamentos' NO encontrada\n";
    }
    
    // Verificar tabla recibo_gasto_comuns (nombre correcto)
     if (in_array('recibo_gasto_comuns', $tables)) {
         echo "\n✓ Tabla 'recibo_gasto_comuns' encontrada\n";
         
         $stmt = $pdo->query("SELECT COUNT(*) FROM recibo_gasto_comuns");
         $count = $stmt->fetchColumn();
         echo "Total de recibos: $count\n";
         
         if ($count > 0) {
             // Mostrar algunos recibos
             $stmt = $pdo->query("SELECT id, numero_recibo, apartamento_id, total_recibo, estado FROM recibo_gasto_comuns LIMIT 5");
             $recibos = $stmt->fetchAll(PDO::FETCH_ASSOC);
             
             echo "\nPrimeros 5 recibos:\n";
             foreach ($recibos as $recibo) {
                 echo "- ID: {$recibo['id']}, Número: {$recibo['numero_recibo']}, Apartamento: {$recibo['apartamento_id']}, Total: {$recibo['total_recibo']}, Estado: {$recibo['estado']}\n";
             }
         }
         
         // Buscar recibo 264
         $stmt = $pdo->prepare("SELECT * FROM recibo_gasto_comuns WHERE id = 264");
         $stmt->execute();
         $recibo264 = $stmt->fetch(PDO::FETCH_ASSOC);
         
         if ($recibo264) {
             echo "\n✓ Recibo 264 encontrado:\n";
             echo "- Número: {$recibo264['numero_recibo']}\n";
             echo "- Apartamento ID: {$recibo264['apartamento_id']}\n";
             echo "- Total: {$recibo264['total_recibo']}\n";
             echo "- Estado: {$recibo264['estado']}\n";
         } else {
             echo "\n✗ Recibo 264 NO encontrado\n";
         }
     } else {
         echo "\n✗ Tabla 'recibo_gasto_comuns' NO encontrada\n";
     }
    
    // Verificar tabla pagos
    if (in_array('pagos', $tables)) {
        echo "\n✓ Tabla 'pagos' encontrada\n";
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM pagos");
        $count = $stmt->fetchColumn();
        echo "Total de pagos: $count\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== FIN VERIFICACIÓN ===\n";