-- Script para resolver el problema de la tabla egresos

-- Paso 1: Eliminar cualquier archivo de tablespace huérfano
SET foreign_key_checks = 0;

-- Paso 2: Eliminar tabla si existe
DROP TABLE IF EXISTS egresos;

-- Paso 3: Crear tabla egresos con estructura correcta
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
    INDEX idx_proveedor_id (proveedor_id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Paso 4: Agregar foreign key constraint
ALTER TABLE egresos 
ADD CONSTRAINT fk_egresos_proveedor 
FOREIGN KEY (proveedor_id) REFERENCES proveedors(id) ON DELETE SET NULL;

-- Paso 5: Copiar datos de tabla temporal si existe
INSERT IGNORE INTO egresos (id, nro_factura, fecha, comprobante, monto, descripcion, proveedor_id, monto_en_bs, created_at, updated_at) 
SELECT id, nro_factura, fecha, comprobante, monto, descripcion, proveedor_id, monto_en_bs, created_at, updated_at 
FROM egresos_temp_1759253507
WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'smart_comunity' AND table_name = 'egresos_temp_1759253507');

-- Restaurar foreign key checks
SET foreign_key_checks = 1;

-- Verificar que la tabla existe
SELECT 'Tabla egresos creada exitosamente' as resultado;
SELECT COUNT(*) as total_registros FROM egresos;