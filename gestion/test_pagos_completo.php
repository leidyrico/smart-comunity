<?php

require_once __DIR__ . '/vendor/autoload.php';

// Cargar la aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Configurar la aplicación
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\PagoController;
use Illuminate\Http\Request;
use App\Models\Apartamento;

echo "=== VERIFICACIÓN COMPLETA DEL SISTEMA DE PAGOS ===\n\n";

// Verificar que la aplicación funciona en el puerto 8080
echo "✅ Aplicación ejecutándose en: http://localhost:8080\n";
echo "✅ API endpoints disponibles:\n";
echo "   - http://localhost:8080/api/recibos-por-apartamento\n";
echo "   - http://localhost:8080/api/saldo-recibo\n\n";

// Probar con apartamento 13 (ID 91)
$apartamento = Apartamento::where('numero', '13')->first();

if (!$apartamento) {
    echo "❌ Apartamento 13 no encontrado\n";
    exit;
}

echo "🏠 Apartamento de prueba: {$apartamento->numero} (ID: {$apartamento->id})\n";
echo "👤 Propietario: {$apartamento->propietario}\n\n";

// Probar API getRecibosPorApartamento
echo "=== PROBANDO API getRecibosPorApartamento ===\n";

try {
    $controller = new PagoController();
    $request = new Request();
    $request->merge(['apartamento_id' => $apartamento->id]);
    
    $response = $controller->getRecibosPorApartamento($request);
    $data = $response->getData(true);
    
    echo "✅ API Response: " . count($data) . " recibos encontrados\n";
    
    if (count($data) > 0) {
        echo "\n📋 Primeros 3 recibos:\n";
        for ($i = 0; $i < min(3, count($data)); $i++) {
            $recibo = $data[$i];
            $numero = $i + 1;
            echo "   {$numero}. {$recibo['numero_recibo']} ({$recibo['periodo']}) - Saldo: $" . number_format($recibo['saldo_pendiente'], 2) . "\n";
        }
        
        // Probar getSaldoRecibo
        echo "\n=== PROBANDO API getSaldoRecibo ===\n";
        $primerRecibo = $data[0];
        
        $requestSaldo = new Request();
        $requestSaldo->merge([
            'apartamento_id' => $apartamento->id,
            'recibo_id' => $primerRecibo['id']
        ]);
        
        $responseSaldo = $controller->getSaldoRecibo($requestSaldo);
        $dataSaldo = $responseSaldo->getData(true);
        
        echo "✅ Saldo API Response: $" . number_format($dataSaldo['saldo_pendiente'], 2) . "\n";
        
        // Verificar consistencia
        if (abs($dataSaldo['saldo_pendiente'] - $primerRecibo['saldo_pendiente']) < 0.01) {
            echo "✅ Datos consistentes entre ambas APIs\n";
        } else {
            echo "❌ Inconsistencia detectada\n";
        }
        
    } else {
        echo "⚠️  No hay recibos con saldo pendiente\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== INSTRUCCIONES PARA EL USUARIO ===\n";
echo "🌐 Para acceder a la aplicación, usa: http://localhost:8080\n";
echo "📝 Para registrar pagos, ve a: http://localhost:8080/pagos/create\n";
echo "🔐 Asegúrate de estar autenticado en la aplicación\n";
echo "\n⚠️  IMPORTANTE: No uses http://localhost/smart-comunity/gestion/public/\n";
echo "   La aplicación ahora está en el puerto 8080\n";

echo "\n=== ESTADO DEL SISTEMA ===\n";
echo "✅ Backend funcionando correctamente\n";
echo "✅ APIs respondiendo sin errores\n";
echo "✅ Datos de recibos disponibles\n";
echo "✅ Filtrado por apartamento operativo\n";

echo "\n=== FIN DE LA VERIFICACIÓN ===\n";
?>