# Solución para Problemas de Correo SMTP

## Problema Original

**Error:** `Maximum execution time of 60 seconds exceeded` en `AbstractStream.php:82`

**Descripción:** El sistema experimentaba timeouts de 60 segundos al intentar enviar correos electrónicos a través de SMTP con Gmail, causando que la aplicación fallara durante el envío masivo de recibos.

## Diagnóstico Realizado

### 1. Análisis del Error
- **Archivo afectado:** `vendor/symfony/mailer/Transport/Smtp/Stream/AbstractStream.php` línea 82
- **Causa:** Timeout de conexión SMTP durante el envío de correos
- **Configuración original:** Puerto 587 con TLS

### 2. Pruebas de Conectividad
- ✅ Conexión básica a `smtp.gmail.com:587` exitosa
- ✅ Credenciales de Gmail válidas
- ❌ Timeout durante el proceso de envío con TLS

## Soluciones Implementadas

### 1. Cambio de Configuración SMTP

**Archivo:** `.env`
```env
# Configuración anterior (problemática)
MAIL_PORT=587
MAIL_ENCRYPTION=tls

# Configuración nueva (funcional)
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
```

**Archivo:** `config/mail.php`
```php
'smtp' => [
    'transport' => 'smtp',
    'url' => env('MAIL_URL'),
    'host' => env('MAIL_HOST', '127.0.0.1'),
    'port' => env('MAIL_PORT', 465), // Cambiado de 2525 a 465
    'encryption' => env('MAIL_ENCRYPTION', 'ssl'),
    'username' => env('MAIL_USERNAME'),
    'password' => env('MAIL_PASSWORD'),
    'timeout' => 120, // Aumentado de null a 120 segundos
    'auth_mode' => null,
    'verify_peer' => false, // Agregado para evitar problemas SSL
],
```

### 2. Manejo de Errores Mejorado

**Archivo:** `app/Http/Controllers/ReciboGastoComunController.php`

```php
// Enviar correo masivo solo si está marcado el checkbox
if ($request->has('enviar_correo') && $request->enviar_correo) {
    try {
        $emailService = new EmailMasivoService();
        $resultado = $emailService->enviarCorreoMasivo($recibo, $recibo->archivo_adjunto);
        
        // Logging detallado
        \Log::info('Resultado envío masivo', [
            'recibo_id' => $recibo->id,
            'success' => $resultado['success'],
            'message' => $resultado['message'],
            'total_emails' => $resultado['total_emails'],
            'archivo_adjunto' => $recibo->archivo_adjunto ? 'Sí' : 'No'
        ]);
        
        // Mensajes de feedback al usuario
        if ($resultado['success']) {
            session()->flash('email_success', 'Correos enviados exitosamente a ' . $resultado['total_emails'] . ' apartamentos.');
        } else {
            session()->flash('email_warning', 'Algunos correos no pudieron enviarse: ' . $resultado['message']);
        }
        
    } catch (\Exception $e) {
        \Log::error('Error crítico en envío masivo de correos', [
            'recibo_id' => $recibo->id,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        // Notificar al usuario del error sin interrumpir el flujo
        session()->flash('email_error', 'Error al enviar correos: ' . $e->getMessage() . '. El recibo se creó correctamente.');
    }
}
```

### 3. Interfaz de Usuario Mejorada

**Archivo:** `resources/views/recibos/index.blade.php`

Se agregaron mensajes de estado específicos para el envío de correos:

- **Éxito:** Mensaje azul con ícono de correo
- **Advertencia:** Mensaje amarillo para envíos parciales
- **Error:** Mensaje rojo para errores críticos

## Resultados de las Pruebas

### Prueba de Envío Exitosa
```
✅ ¡Correo enviado exitosamente!
⏱️ Tiempo de ejecución: 1.61 segundos

Configuración utilizada:
- Host: smtp.gmail.com
- Puerto: 465
- Encriptación: ssl
- Timeout: 120 segundos
- Usuario: residenciasalfa.quenda@gmail.com
```

## Configuración Recomendada para Gmail

### Opción 1: SSL (Puerto 465) - **RECOMENDADA**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
MAIL_USERNAME=tu_email@gmail.com
MAIL_PASSWORD="tu_app_password"
```

### Opción 2: STARTTLS (Puerto 587) - Alternativa
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=tu_email@gmail.com
MAIL_PASSWORD="tu_app_password"
```

## Requisitos Previos

1. **Contraseña de Aplicación de Gmail:**
   - Activar autenticación de 2 factores en Gmail
   - Generar una contraseña de aplicación específica
   - Usar esta contraseña en `MAIL_PASSWORD`

2. **Configuración del Servidor:**
   - PHP con extensión OpenSSL habilitada
   - Firewall configurado para permitir conexiones SMTP salientes
   - Timeout de PHP configurado adecuadamente

## Monitoreo y Logs

### Archivos de Log a Revisar
- `storage/logs/laravel.log` - Logs de la aplicación
- Logs del servidor web (Apache/Nginx)
- Logs de PHP

### Comandos Útiles para Debugging
```bash
# Verificar configuración de correo
php artisan tinker
>>> config('mail.mailers.smtp')

# Probar envío de correo simple
php artisan tinker
>>> Mail::raw('Prueba', function($m) { $m->to('test@example.com')->subject('Test'); });
```

## Prevención de Problemas Futuros

1. **Monitoreo Regular:** Revisar logs de envío de correos semanalmente
2. **Pruebas Periódicas:** Ejecutar pruebas de envío mensualmente
3. **Backup de Configuración:** Mantener respaldo de configuraciones funcionales
4. **Documentación:** Mantener este documento actualizado con cambios

## Contacto y Soporte

En caso de problemas similares:
1. Revisar este documento primero
2. Verificar logs de la aplicación
3. Probar configuración con script de prueba
4. Contactar al equipo de desarrollo si persisten los problemas

---

**Fecha de Resolución:** Enero 2025  
**Estado:** ✅ Resuelto  
**Tiempo de Envío Actual:** ~1.6 segundos  
**Configuración Estable:** Puerto 465 con SSL