# 📧 Guía para Configurar Gmail y Enviar Correos Reales

## 🎯 Objetivo
Configurar el sistema para enviar correos reales usando Gmail cuando se procesen pagos.

## ⚠️ Estado Actual
- **MAIL_MAILER=log** (los correos se guardan en log, no se envían)
- **Necesitas cambiar a MAIL_MAILER=smtp** para envío real

## 🔧 Pasos para Configurar Gmail

### Paso 1: Generar Contraseña de Aplicación en Gmail

1. **Ve a tu cuenta de Google**: https://myaccount.google.com/security
2. **Activa la verificación en 2 pasos** (si no está activada)
3. **Ve a contraseñas de aplicación**: https://myaccount.google.com/apppasswords
4. **Selecciona "Correo"** como aplicación
5. **Copia la contraseña de 16 caracteres** que se genera

### Paso 2: Actualizar el archivo .env

Abre el archivo `.env` y cambia estas líneas:

```env
# Cambiar de 'log' a 'smtp'
MAIL_MAILER=smtp

# Configuración Gmail
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=residenciasalfa.quenda@gmail.com
MAIL_PASSWORD=tu_contraseña_de_aplicacion_16_caracteres
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@residenciasalfa.com"
MAIL_FROM_NAME="Residencias Alfa"
```

### Paso 3: Limpiar Caché de Laravel

```bash
php artisan config:clear
```

### Paso 4: Probar la Configuración

Ejecuta este comando para probar:

```bash
php test_email_real.php
```

## 🧪 Script de Prueba Rápida

Si necesitas probar rápidamente, crea este archivo `test_gmail.php`:

```php
<?php
require_once __DIR__ . "/vendor/autoload.php";

$app = require_once __DIR__ . "/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;
use App\Mail\ComprobantePago;
use App\Models\Pago;

echo "🧪 Probando envío con Gmail...\n";

try {
    // Crear pago de prueba
    $pago = new Pago();
    $pago->apartamento = "TEST-001";
    $pago->inquilino_nombre = "Prueba";
    $pago->monto = 100;
    $pago->fecha_pago = now();
    $pago->metodo_pago = "Test";
    $pago->referencia = "TEST-" . time();
    
    // Cambiar por tu email real
    $emailPrueba = "tu_email@gmail.com";
    
    Mail::to($emailPrueba)->send(new ComprobantePago($pago));
    
    echo "✅ ¡Correo enviado exitosamente!\n";
    echo "📧 Revisa tu bandeja de entrada\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    
    if (strpos($e->getMessage(), "535") !== false) {
        echo "\n🔧 Solución: Verifica tu contraseña de aplicación\n";
        echo "- Debe ser de 16 caracteres\n";
        echo "- Generada en https://myaccount.google.com/apppasswords\n";
    }
}
?>
```

## ✅ Verificación Final

Después de configurar:

1. **Realiza un pago global** en `/deudas`
2. **Verifica que aparezca**: "Pago registrado exitosamente y correo de confirmación enviado"
3. **Revisa tu email** (y carpeta de spam)
4. **Deberías recibir** el correo de confirmación

## 🚨 Problemas Comunes

### Error 535 - Credenciales no aceptadas
- ❌ **NO uses tu contraseña normal de Gmail**
- ✅ **USA la contraseña de aplicación de 16 caracteres**
- ✅ **Verifica que la verificación en 2 pasos esté activada**

### Error de conexión
- Verifica tu conexión a internet
- Verifica que el puerto 587 no esté bloqueado

### Correo no llega
- Revisa la carpeta de spam
- Verifica que `MAIL_FROM_ADDRESS` sea válido

## 🔄 Volver al Modo Log

Si quieres volver al modo de solo registrar (sin enviar):

```env
MAIL_MAILER=log
```

Y ejecuta: `php artisan config:clear`

## 📞 Soporte

Si tienes problemas:
1. Verifica que seguiste todos los pasos
2. Ejecuta `php test_gmail.php` para diagnóstico
3. Revisa los logs en `storage/logs/laravel.log`