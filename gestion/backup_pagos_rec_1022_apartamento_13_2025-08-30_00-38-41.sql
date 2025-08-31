-- Backup de pagos REC-1022 apartamento 13 - 2025-08-30_00-38-41
-- Ejecutar este script para restaurar los pagos si es necesario

UPDATE pagos SET estado = 'confirmado', observaciones = 'JULIO + ABONO DE DEUDAS | Pago global distribuido automáticamente' WHERE id = 79;
