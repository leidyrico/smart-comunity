<?php
/**
 * Script para probar el nuevo servicio de envío masivo optimizado
 */

require_once __DIR__ . "/vendor/autoload.php";

$app = require_once __DIR__ . "/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\EmailMasivoService;
use App\Models\ReciboGastoComun;
use App\Models\Apartamento;

echo "\n🚀 PRUEBA DE SERVICIO DE ENVÍO MASIVO CON ARCHIVO ADJUNTO\n";
echo "========================================================\n\n";

// Crear instancia del servicio
$emailService = new EmailMasivoService();

// Mostrar estadísticas de emails
echo "📊 ESTADÍSTICAS DE EMAILS:\n";
$estadisticas = $emailService->obtenerEstadisticasEmails();
foreach ($estadisticas as $clave => $valor) {
    echo "- " . ucfirst(str_replace('_', ' ', $clave)) . ": $valor\n";
}
echo "\n";

// Crear un recibo de prueba con archivo adjunto
echo "📋 CREANDO RECIBO DE PRUEBA CON ARCHIVO ADJUNTO...\n";

// Crear un archivo PDF de prueba
$archivoTest = 'recibos/test_recibo_' . date('YmdHis') . '.pdf';
$rutaCompleta = storage_path('app/public/' . $archivoTest);

// Crear directorio si no existe
if (!file_exists(dirname($rutaCompleta))) {
    mkdir(dirname($rutaCompleta), 0755, true);
}

// Crear un PDF simple de prueba
file_put_contents($rutaCompleta, "%PDF-1.4\n1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox [0 0 612 792]\n>>\nendobj\nxref\n0 4\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \ntrailer\n<<\n/Size 4\n/Root 1 0 R\n>>\nstartxref\n174\n%%EOF");

// Buscar un recibo existente o crear uno nuevo
$recibo = ReciboGastoComun::latest()->first();

if (!$recibo) {
    echo "No se encontraron recibos, creando uno nuevo...\n";
    $recibo = new ReciboGastoComun([
        'numero_recibo' => 'TEST-' . date('YmdHis'),
        'periodo' => date('Y-m'),
        'fecha_emision' => now(),
        'fecha_vencimiento' => now()->addDays(30),
        'valor_administracion' => 150000,
        'valor_aseo' => 25000,
        'valor_vigilancia' => 30000,
        'valor_mantenimiento' => 20000,
        'otros_conceptos' => 5000,
        'estado' => 'activo',
        'observaciones' => 'Recibo de prueba para envío masivo con archivo adjunto'
    ]);
    $recibo->calcularTotal();
    $recibo->save();
}

// Asignar archivo adjunto al recibo
$recibo->archivo_adjunto = $archivoTest;
$recibo->save();

echo "📋 RECIBO DE PRUEBA:\n";
echo "- Número: {$recibo->numero_recibo}\n";
echo "- Período: {$recibo->periodo}\n";
echo "- Total: $" . number_format($recibo->total_recibo, 2) . "\n";
echo "- Archivo adjunto: {$recibo->archivo_adjunto}\n";
echo "- Archivo existe: " . (file_exists($rutaCompleta) ? 'Sí' : 'No') . "\n\n";

// Verificar configuración de correo
echo "📧 CONFIGURACIÓN DE CORREO:\n";
echo "- Mailer: " . config('mail.default') . "\n";
echo "- Host: " . config('mail.mailers.smtp.host') . "\n";
echo "- Username: " . config('mail.mailers.smtp.username') . "\n\n";

if (config('mail.default') !== 'smtp') {
    echo "⚠️  ADVERTENCIA: El sistema está en modo '" . config('mail.default') . "'\n";
    echo "Los correos se registrarán en logs pero no se enviarán realmente\n\n";
}

echo "⏱️  INICIANDO ENVÍO MASIVO...\n";
$tiempoInicio = microtime(true);

try {
    // Ejecutar envío masivo con archivo adjunto
    $resultado = $emailService->enviarCorreoMasivo($recibo, $recibo->archivo_adjunto);
    
    $tiempoFin = microtime(true);
    $tiempoTotal = round($tiempoFin - $tiempoInicio, 2);
    
    echo "\n✅ RESULTADO DEL ENVÍO:\n";
    echo "- Estado: " . ($resultado['success'] ? 'EXITOSO' : 'FALLIDO') . "\n";
    echo "- Mensaje: {$resultado['message']}\n";
    echo "- Total emails: {$resultado['total_emails']}\n";
    echo "- Archivo adjunto: " . ($recibo->archivo_adjunto ? 'Incluido' : 'No incluido') . "\n";
    echo "- Tiempo total: {$tiempoTotal} segundos\n\n";
    
    if ($resultado['success'] && isset($resultado['emails'])) {
        echo "📧 EMAILS PROCESADOS:\n";
        foreach (array_slice($resultado['emails'], 0, 5) as $email) {
            echo "- $email\n";
        }
        if (count($resultado['emails']) > 5) {
            echo "- ... y " . (count($resultado['emails']) - 5) . " más\n";
        }
        echo "\n";
    }
    
    // Comparar con método anterior
    echo "📈 COMPARACIÓN DE RENDIMIENTO:\n";
    echo "- Método anterior: ~" . ($resultado['total_emails'] * 2) . " segundos (estimado)\n";
    echo "- Método optimizado: {$tiempoTotal} segundos\n";
    echo "- Mejora: ~" . round((($resultado['total_emails'] * 2) - $tiempoTotal), 1) . " segundos más rápido\n\n";
    
    if ($tiempoTotal < 10) {
        echo "🎉 ¡EXCELENTE! El envío masivo es mucho más rápido\n";
    } elseif ($tiempoTotal < 30) {
        echo "✅ BUENO: El envío masivo está dentro del tiempo aceptable\n";
    } else {
        echo "⚠️  ADVERTENCIA: El envío aún toma tiempo, revisa la configuración SMTP\n";
    }
    
} catch (Exception $e) {
    $tiempoFin = microtime(true);
    $tiempoTotal = round($tiempoFin - $tiempoInicio, 2);
    
    echo "\n❌ ERROR EN EL ENVÍO:\n";
    echo "- Mensaje: " . $e->getMessage() . "\n";
    echo "- Tiempo antes del error: {$tiempoTotal} segundos\n\n";
    
    echo "🔧 POSIBLES SOLUCIONES:\n";
    echo "1. Verificar configuración SMTP en .env\n";
    echo "2. Comprobar contraseña de aplicación de Gmail\n";
    echo "3. Revisar logs en storage/logs/laravel.log\n";
    echo "4. Usar modo 'log' para pruebas: MAIL_MAILER=log\n";
}

// Limpiar datos de prueba
echo "\n🧹 LIMPIANDO DATOS DE PRUEBA...\n";
// Eliminar archivo de prueba
if (file_exists($rutaCompleta)) {
    unlink($rutaCompleta);
    echo "Archivo de prueba eliminado.\n";
}
echo "\n🔄 PARA USAR EN PRODUCCIÓN:\n";
echo "1. Configura MAIL_MAILER=smtp en .env\n";
echo "2. Usa una contraseña de aplicación válida de Gmail\n";
echo "3. Ejecuta: php artisan config:clear\n";
echo "4. Los recibos se enviarán automáticamente al crearlos\n";
echo "5. Los archivos adjuntos se incluirán automáticamente si están configurados\n";