<?php
/**
 * Script para configurar el envío real de correos electrónicos
 * Este script ayuda a configurar SMTP para enviar correos reales
 */

echo "\n=== CONFIGURACIÓN DE CORREO REAL ===\n";
echo "Actualmente el sistema usa MAIL_MAILER=log (no envía correos reales)\n";
echo "Este script te ayudará a configurar SMTP para envío real.\n\n";

// Leer configuración actual
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    echo "❌ Error: No se encontró el archivo .env\n";
    exit(1);
}

$envContent = file_get_contents($envFile);
echo "📋 CONFIGURACIÓN ACTUAL:\n";
echo "MAIL_MAILER: " . (preg_match('/MAIL_MAILER=(.*)/', $envContent, $matches) ? $matches[1] : 'no configurado') . "\n";
echo "MAIL_HOST: " . (preg_match('/MAIL_HOST=(.*)/', $envContent, $matches) ? $matches[1] : 'no configurado') . "\n";
echo "MAIL_USERNAME: " . (preg_match('/MAIL_USERNAME=(.*)/', $envContent, $matches) ? $matches[1] : 'no configurado') . "\n\n";

echo "🔧 OPCIONES DE CONFIGURACIÓN:\n";
echo "1. Gmail con contraseña de aplicación (recomendado para producción)\n";
echo "2. Mailtrap (recomendado para desarrollo/testing)\n";
echo "3. Otro servicio SMTP\n";
echo "4. Mostrar instrucciones detalladas\n";
echo "5. Salir\n\n";

echo "Selecciona una opción (1-5): ";
$option = trim(fgets(STDIN));

switch ($option) {
    case '1':
        configureGmail();
        break;
    case '2':
        configureMailtrap();
        break;
    case '3':
        configureCustomSMTP();
        break;
    case '4':
        showDetailedInstructions();
        break;
    case '5':
        echo "Saliendo...\n";
        exit(0);
    default:
        echo "❌ Opción no válida\n";
        exit(1);
}

function configureGmail() {
    echo "\n📧 CONFIGURACIÓN DE GMAIL\n";
    echo "Para usar Gmail necesitas:\n";
    echo "1. Activar verificación en 2 pasos en tu cuenta Google\n";
    echo "2. Generar una contraseña de aplicación\n\n";
    
    echo "¿Ya tienes una contraseña de aplicación? (s/n): ";
    $hasAppPassword = trim(fgets(STDIN));
    
    if (strtolower($hasAppPassword) !== 's') {
        echo "\n🔗 PASOS PARA GENERAR CONTRASEÑA DE APLICACIÓN:\n";
        echo "1. Ve a https://myaccount.google.com/security\n";
        echo "2. Activa 'Verificación en 2 pasos' si no está activada\n";
        echo "3. Ve a https://myaccount.google.com/apppasswords\n";
        echo "4. Genera una nueva contraseña para 'Correo'\n";
        echo "5. Copia la contraseña de 16 caracteres\n\n";
        echo "Presiona Enter cuando tengas la contraseña de aplicación...";
        fgets(STDIN);
    }
    
    echo "\nIngresa tu email de Gmail: ";
    $email = trim(fgets(STDIN));
    
    echo "Ingresa tu contraseña de aplicación (16 caracteres): ";
    $password = trim(fgets(STDIN));
    
    if (strlen($password) !== 16) {
        echo "⚠️  Advertencia: Las contraseñas de aplicación de Gmail tienen 16 caracteres\n";
    }
    
    updateEnvFile([
        'MAIL_MAILER' => 'smtp',
        'MAIL_HOST' => 'smtp.gmail.com',
        'MAIL_PORT' => '587',
        'MAIL_USERNAME' => $email,
        'MAIL_PASSWORD' => $password,
        'MAIL_ENCRYPTION' => 'tls'
    ]);
    
    echo "\n✅ Configuración de Gmail guardada\n";
    testConfiguration();
}

function configureMailtrap() {
    echo "\n📨 CONFIGURACIÓN DE MAILTRAP\n";
    echo "Mailtrap es ideal para desarrollo y testing\n";
    echo "Registrate en https://mailtrap.io para obtener credenciales\n\n";
    
    echo "Ingresa tu username de Mailtrap: ";
    $username = trim(fgets(STDIN));
    
    echo "Ingresa tu password de Mailtrap: ";
    $password = trim(fgets(STDIN));
    
    updateEnvFile([
        'MAIL_MAILER' => 'smtp',
        'MAIL_HOST' => 'sandbox.smtp.mailtrap.io',
        'MAIL_PORT' => '2525',
        'MAIL_USERNAME' => $username,
        'MAIL_PASSWORD' => $password,
        'MAIL_ENCRYPTION' => 'tls'
    ]);
    
    echo "\n✅ Configuración de Mailtrap guardada\n";
    testConfiguration();
}

function configureCustomSMTP() {
    echo "\n🔧 CONFIGURACIÓN SMTP PERSONALIZADA\n";
    
    echo "Ingresa el host SMTP: ";
    $host = trim(fgets(STDIN));
    
    echo "Ingresa el puerto (587, 465, 25): ";
    $port = trim(fgets(STDIN));
    
    echo "Ingresa el username: ";
    $username = trim(fgets(STDIN));
    
    echo "Ingresa la contraseña: ";
    $password = trim(fgets(STDIN));
    
    echo "Ingresa el tipo de encriptación (tls/ssl): ";
    $encryption = trim(fgets(STDIN));
    
    updateEnvFile([
        'MAIL_MAILER' => 'smtp',
        'MAIL_HOST' => $host,
        'MAIL_PORT' => $port,
        'MAIL_USERNAME' => $username,
        'MAIL_PASSWORD' => $password,
        'MAIL_ENCRYPTION' => $encryption
    ]);
    
    echo "\n✅ Configuración SMTP personalizada guardada\n";
    testConfiguration();
}

function updateEnvFile($config) {
    $envFile = __DIR__ . '/.env';
    $envContent = file_get_contents($envFile);
    
    foreach ($config as $key => $value) {
        $pattern = '/^' . preg_quote($key) . '=.*$/m';
        $replacement = $key . '=' . $value;
        
        if (preg_match($pattern, $envContent)) {
            $envContent = preg_replace($pattern, $replacement, $envContent);
        } else {
            $envContent .= "\n" . $replacement;
        }
    }
    
    file_put_contents($envFile, $envContent);
    echo "📝 Archivo .env actualizado\n";
}

function testConfiguration() {
    echo "\n🧪 ¿Quieres probar la configuración ahora? (s/n): ";
    $test = trim(fgets(STDIN));
    
    if (strtolower($test) === 's') {
        echo "\n⚡ Ejecuta este comando para probar:\n";
        echo "php artisan config:clear && php test_email_real.php\n\n";
        
        // Crear script de prueba
        createTestScript();
        
        echo "✅ Script de prueba creado: test_email_real.php\n";
        echo "📋 PRÓXIMOS PASOS:\n";
        echo "1. Ejecuta: php artisan config:clear\n";
        echo "2. Ejecuta: php test_email_real.php\n";
        echo "3. Verifica que recibas el correo de prueba\n";
    }
}

function createTestScript() {
    $testScript = '<?php
require_once __DIR__ . "/vendor/autoload.php";

$app = require_once __DIR__ . "/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;
use App\Mail\ComprobantePago;
use App\Models\Pago;

echo "\n🧪 PRUEBA DE ENVÍO DE CORREO REAL\n";
echo "================================\n\n";

try {
    // Obtener configuración actual
    echo "📋 Configuración actual:\n";
    echo "MAIL_MAILER: " . config("mail.default") . "\n";
    echo "MAIL_HOST: " . config("mail.mailers.smtp.host") . "\n";
    echo "MAIL_PORT: " . config("mail.mailers.smtp.port") . "\n";
    echo "MAIL_USERNAME: " . config("mail.mailers.smtp.username") . "\n\n";
    
    // Solicitar email de destino
    echo "Ingresa el email donde quieres recibir la prueba: ";
    $emailDestino = trim(fgets(STDIN));
    
    if (!filter_var($emailDestino, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Email no válido");
    }
    
    // Crear un pago de prueba
    $pagoTest = new Pago();
    $pagoTest->id = 999999;
    $pagoTest->apartamento = "PRUEBA-001";
    $pagoTest->inquilino_nombre = "Usuario de Prueba";
    $pagoTest->monto = 100.00;
    $pagoTest->fecha_pago = now();
    $pagoTest->metodo_pago = "Transferencia";
    $pagoTest->referencia = "TEST-" . time();
    
    echo "📤 Enviando correo de prueba a: $emailDestino\n";
    
    Mail::to($emailDestino)->send(new ComprobantePago($pagoTest));
    
    echo "\n✅ ¡CORREO ENVIADO EXITOSAMENTE!\n";
    echo "📧 Revisa tu bandeja de entrada (y spam) en: $emailDestino\n";
    echo "📋 Asunto: Confirmación de pago recibido\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR AL ENVIAR CORREO:\n";
    echo $e->getMessage() . "\n\n";
    
    if (strpos($e->getMessage(), "535") !== false) {
        echo "🔧 POSIBLES SOLUCIONES:\n";
        echo "1. Verifica que el usuario y contraseña sean correctos\n";
        echo "2. Para Gmail: usa contraseña de aplicación, no tu contraseña normal\n";
        echo "3. Verifica que la verificación en 2 pasos esté activada (Gmail)\n";
    } elseif (strpos($e->getMessage(), "Connection") !== false) {
        echo "🔧 POSIBLES SOLUCIONES:\n";
        echo "1. Verifica la conexión a internet\n";
        echo "2. Verifica el host y puerto SMTP\n";
        echo "3. Verifica la configuración de firewall\n";
    }
}
?>';
    
    file_put_contents(__DIR__ . '/test_email_real.php', $testScript);
}

function showDetailedInstructions() {
    echo "\n📖 INSTRUCCIONES DETALLADAS\n";
    echo "===========================\n\n";
    
    echo "🔵 OPCIÓN 1: GMAIL (Recomendado para producción)\n";
    echo "Ventajas: Confiable, gratuito hasta 100 correos/día\n";
    echo "Pasos:\n";
    echo "1. Ve a https://myaccount.google.com/security\n";
    echo "2. Activa 'Verificación en 2 pasos'\n";
    echo "3. Ve a https://myaccount.google.com/apppasswords\n";
    echo "4. Genera contraseña para 'Correo'\n";
    echo "5. Usa esa contraseña de 16 caracteres\n\n";
    
    echo "🟡 OPCIÓN 2: MAILTRAP (Recomendado para desarrollo)\n";
    echo "Ventajas: Perfecto para testing, no envía correos reales\n";
    echo "Pasos:\n";
    echo "1. Registrate en https://mailtrap.io\n";
    echo "2. Crea un inbox\n";
    echo "3. Copia las credenciales SMTP\n\n";
    
    echo "🟢 OPCIÓN 3: OTROS SERVICIOS\n";
    echo "- SendGrid: Hasta 100 correos/día gratis\n";
    echo "- Mailgun: Hasta 5000 correos/mes gratis\n";
    echo "- Amazon SES: Muy económico para volumen alto\n\n";
    
    echo "⚠️  IMPORTANTE:\n";
    echo "- Nunca uses tu contraseña normal de Gmail\n";
    echo "- Siempre usa contraseñas de aplicación\n";
    echo "- Prueba la configuración antes de usar en producción\n\n";
}

echo "\n🎯 CONFIGURACIÓN COMPLETADA\n";
echo "Recuerda ejecutar 'php artisan config:clear' después de cambios\n";
?>