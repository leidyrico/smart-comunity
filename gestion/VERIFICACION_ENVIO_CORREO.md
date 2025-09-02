# Verificación del Funcionamiento del Botón "Enviar por Correo"

## ¿Cómo saber si el botón funciona?

Cuando presiones el botón "Enviar por Correo" en la página de deudas, deberías ver uno de estos mensajes:

### ✅ Mensaje de Éxito (Verde)
```
Reporte enviado exitosamente a [email_destino]
```

### ❌ Mensaje de Error (Rojo)
```
Error al enviar el reporte: [descripción del error]
```

## Configuración Actual del Sistema

El sistema está configurado para usar el driver `log` para el envío de correos, lo que significa que:

- **NO se envían correos reales** por defecto
- Los correos se guardan en archivos de log para verificación
- Es la configuración ideal para desarrollo y testing

## ¿Dónde verificar que se "envió" el correo?

### 1. Mensaje en Pantalla
Después de presionar "Enviar por Correo", deberías ver el mensaje verde de éxito.

### 2. Archivo de Log de Laravel
Los correos se guardan en:
```
storage/logs/laravel.log
```

Busca líneas que contengan:
- `local.INFO: Message sent`
- El contenido del correo HTML
- La información del destinatario

### 3. Verificación Manual
Puedes abrir el archivo `storage/logs/laravel.log` y buscar las entradas más recientes para ver el correo "enviado".

## Lógica de Destinatarios

El sistema determina el email de destino así:

1. **Si hay filtro de apartamento específico Y el apartamento tiene email:**
   - Envía al email del apartamento
   - Mensaje: "Reporte enviado exitosamente a [email_apartamento]"

2. **Si NO hay apartamento específico O el apartamento no tiene email:**
   - Envía al administrador: `admin@sc.com`
   - Mensaje: "Reporte enviado exitosamente a admin@sc.com"

## Para Envío Real de Correos

Si quieres configurar el envío real de correos:

1. Modifica el archivo `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=tu_servidor_smtp
MAIL_PORT=587
MAIL_USERNAME=tu_email
MAIL_PASSWORD=tu_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu_email
MAIL_FROM_NAME="Sistema Residencias Alfa"
```

2. Reinicia el servidor Laravel

## Troubleshooting

### No aparece ningún mensaje
- Verifica que el botón esté enviando el formulario correctamente
- Revisa el archivo `storage/logs/laravel.log` para errores

### Aparece mensaje de error
- Lee el mensaje de error específico
- Verifica la configuración de correo en `.env`
- Revisa los logs para más detalles

### El mensaje dice "enviado" pero no llega el correo
- Verifica que `MAIL_MAILER=smtp` en `.env` (no `log`)
- Confirma la configuración SMTP
- Revisa la carpeta de spam del destinatario