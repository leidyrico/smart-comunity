# Guía de Manejo de Errores de Importación Excel

## Descripción General

Este sistema incluye funcionalidades mejoradas para el manejo y diagnóstico de errores durante la importación de archivos Excel en el módulo de deudas.

## Funcionalidades Implementadas

### 1. Página de Errores Detallados

**Ruta:** `/deudas/import/errors`

**Características:**
- Muestra un resumen de errores por categoría (Apartamentos, Recibos, Pagos)
- Lista detallada de cada error con información específica
- Recomendaciones para corregir errores comunes
- Interfaz amigable con códigos de color por tipo de error

### 2. Logging Mejorado

**Ubicación:** `storage/logs/laravel.log`

**Información registrada:**
- Número de fila donde ocurrió el error
- Datos específicos que causaron el error
- Mensaje de error detallado
- Stack trace completo para debugging

### 3. Comando Artisan para Reportes

**Comando:** `php artisan import:error-report`

**Opciones:**
- `--days=N`: Especifica cuántos días hacia atrás buscar errores (default: 7)

**Ejemplo de uso:**
```bash
# Reporte de los últimos 7 días
php artisan import:error-report

# Reporte de los últimos 30 días
php artisan import:error-report --days=30
```

**Salida:**
- Archivo de reporte en `storage/app/reports/`
- Resumen en consola con estadísticas
- Últimos 3 errores mostrados directamente

## Tipos de Errores Comunes

### Errores de Apartamentos
- **Número duplicado:** El número de apartamento ya existe
- **Datos faltantes:** Campos requeridos vacíos (número, propietario)
- **Formato inválido:** Email o teléfono con formato incorrecto
- **Área inválida:** Área en m² debe ser numérica y positiva

### Errores de Recibos
- **Apartamento no encontrado:** El apartamento referenciado no existe
- **Fecha inválida:** Formato de fecha incorrecto
- **Monto inválido:** Monto debe ser numérico y positivo
- **Período duplicado:** Ya existe un recibo para ese apartamento y período

### Errores de Pagos
- **Recibo no encontrado:** El recibo referenciado no existe
- **Comprobante duplicado:** El número de comprobante ya existe
- **Monto excesivo:** El monto del pago excede la deuda del recibo
- **Fecha inválida:** La fecha de pago es posterior a la fecha actual

## Recomendaciones para Evitar Errores

### Preparación del Archivo Excel

1. **Verificar estructura:**
   - Usar exactamente los nombres de columnas especificados
   - No dejar filas vacías entre datos
   - Eliminar espacios extra en los datos

2. **Validar datos:**
   - Números de apartamento únicos
   - Emails con formato válido (ejemplo@dominio.com)
   - Teléfonos solo con números
   - Fechas en formato DD/MM/YYYY
   - Montos con punto decimal (no comas)

3. **Orden de importación:**
   - Primero apartamentos
   - Luego recibos
   - Finalmente pagos

### Durante la Importación

1. **Revisar mensajes:**
   - Leer cuidadosamente los mensajes de error
   - Usar el enlace "Ver Detalles" para información completa

2. **Corrección iterativa:**
   - Corregir errores por lotes pequeños
   - Re-importar solo las filas corregidas
   - Verificar que no se dupliquen datos ya importados

## Solución de Problemas

### Si la importación falla completamente:

1. **Verificar logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Generar reporte:**
   ```bash
   php artisan import:error-report --days=1
   ```

3. **Verificar permisos:**
   - Archivo Excel debe ser legible
   - Directorio storage debe tener permisos de escritura

### Si algunos registros no se importan:

1. **Acceder a página de errores:**
   - Ir a `/deudas/import/errors` después de la importación
   - Revisar errores específicos por categoría

2. **Corregir archivo Excel:**
   - Usar las recomendaciones mostradas
   - Eliminar o corregir filas problemáticas

3. **Re-importar:**
   - Usar solo las filas corregidas
   - Verificar que no se dupliquen datos

## Contacto y Soporte

Para problemas técnicos adicionales:
- Revisar logs en `storage/logs/laravel.log`
- Generar reporte con `php artisan import:error-report`
- Documentar pasos específicos que causaron el error