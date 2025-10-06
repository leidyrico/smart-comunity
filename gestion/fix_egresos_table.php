<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Configurar la conexión a la base de datos
$capsule = new Capsule;

$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'smart_comunity',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    echo "Iniciando reparación de la tabla egresos...\n";
    
    // Paso 1: Eliminar tablespace si existe
    echo "1. Eliminando tablespace existente...\n";
    try {
        Capsule::statement('ALTER TABLE egresos DISCARD TABLESPACE');
        echo "   Tablespace eliminado.\n";
    } catch (Exception $e) {
        echo "   No se pudo eliminar tablespace (puede que no exista): " . $e->getMessage() . "\n";
    }
    
    // Paso 2: Eliminar tabla si existe
    echo "2. Eliminando tabla egresos si existe...\n";
    try {
        Capsule::statement('DROP TABLE IF EXISTS egresos');
        echo "   Tabla egresos eliminada.\n";
    } catch (Exception $e) {
        echo "   Error eliminando tabla: " . $e->getMessage() . "\n";
    }
    
    // Paso 3: Crear nueva tabla egresos
    echo "3. Creando nueva tabla egresos...\n";
    Capsule::statement('
        CREATE TABLE egresos (
            id bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
            nro_factura varchar(255) NULL,
            fecha date NULL,
            comprobante varchar(255) NULL,
            monto decimal(10, 2) NULL,
            descripcion text NULL,
            proveedor_id bigint unsigned NULL,
            monto_en_bs decimal(15, 2) NULL,
            created_at timestamp NULL,
            updated_at timestamp NULL,
            FOREIGN KEY (proveedor_id) REFERENCES proveedors(id) ON DELETE SET NULL
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ');
    echo "   Tabla egresos creada exitosamente.\n";
    
    // Paso 4: Copiar datos de la tabla temporal si existe
    echo "4. Copiando datos de tabla temporal...\n";
    try {
        $result = Capsule::statement('
            INSERT INTO egresos (id, nro_factura, fecha, comprobante, monto, descripcion, proveedor_id, monto_en_bs, created_at, updated_at) 
            SELECT id, nro_factura, fecha, comprobante, monto, descripcion, proveedor_id, monto_en_bs, created_at, updated_at 
            FROM egresos_temp_1759253507
        ');
        echo "   Datos copiados exitosamente.\n";
    } catch (Exception $e) {
        echo "   No se pudieron copiar datos (tabla temporal puede estar vacía): " . $e->getMessage() . "\n";
    }
    
    // Paso 5: Verificar que la tabla existe
    echo "5. Verificando tabla egresos...\n";
    $tables = Capsule::select('SHOW TABLES LIKE "egresos"');
    if (count($tables) > 0) {
        echo "   ✓ Tabla egresos existe correctamente.\n";
        
        // Contar registros
        $count = Capsule::table('egresos')->count();
        echo "   ✓ La tabla tiene $count registros.\n";
    } else {
        echo "   ✗ Error: La tabla egresos no existe.\n";
    }
    
    echo "\n¡Reparación completada!\n";
    
} catch (Exception $e) {
    echo "Error durante la reparación: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}