<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\ReciboGastoComun;
use App\Models\Apartamento;
use App\Mail\NuevoRecibo;
use Illuminate\Support\Facades\Mail;

// Configurar el entorno de Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== PRUEBA DE ENVÍO DE CORREO PARA RECIBOS ===\n\n";

// Verificar configuración de correo
echo "1. Configuración de correo:\n";
echo "   - Mailer: " . config('mail.default') . "\n";
echo "   - Host: " . config('mail.mailers.smtp.host') . "\n";
echo "   - Port: " . config('mail.mailers.smtp.port') . "\n";
echo "   - Username: " . config('mail.mailers.smtp.username') . "\n";
echo "   - From Address: " . config('mail.from.address') . "\n";
echo "   - From Name: " . config('mail.from.name') . "\n\n";

// Obtener un recibo y apartamento de prueba
echo "2. Buscando recibo y apartamento de prueba:\n";
$recibo = ReciboGastoComun::where('estado', 'activo')->first();
$apartamento = Apartamento::whereNotNull('email')->where('estado', 'ocupado')->first();

if (!$recibo) {
    echo "   ❌ No se encontró ningún recibo activo\n";
    exit(1);
}

if (!$apartamento) {
    echo "   ❌ No se encontró ningún apartamento con email\n";
    exit(1);
}

echo "   ✓ Recibo encontrado: {$recibo->numero_recibo} (ID: {$recibo->id})\n";
echo "   ✓ Apartamento encontrado: {$apartamento->numero} (Email: {$apartamento->email})\n\n";

// Intentar enviar el correo
echo "3. Intentando enviar correo de prueba:\n";
try {
    Mail::to($apartamento->email)->send(new NuevoRecibo($recibo, $apartamento->propietario));
    echo "   ✓ Correo enviado exitosamente a {$apartamento->email}\n";
} catch (\Exception $e) {
    echo "   ❌ Error al enviar correo: " . $e->getMessage() . "\n";
    echo "   Detalles: " . $e->getTraceAsString() . "\n";
}

echo "\n=== FIN DE LA PRUEBA ===\n";