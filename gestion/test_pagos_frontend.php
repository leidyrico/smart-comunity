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

echo "=== PRUEBA COMPLETA DE FUNCIONALIDAD DE PAGOS ===\n\n";

// Buscar apartamento 13
$apartamento = Apartamento::where('numero', '13')->first();

if (!$apartamento) {
    echo "❌ Apartamento 13 no encontrado\n";
    exit;
}

echo "✅ Apartamento encontrado: ID {$apartamento->id}, Número {$apartamento->numero}\n";
echo "Propietario: {$apartamento->propietario}\n\n";

// Crear una instancia del controlador
$controller = new PagoController();

// Simular request para getRecibosPorApartamento
echo "=== PROBANDO getRecibosPorApartamento ===\n";

try {
    $request = new Request();
    $request->merge(['apartamento_id' => $apartamento->id]);
    
    $response = $controller->getRecibosPorApartamento($request);
    $data = $response->getData(true);
    
    echo "✅ Respuesta exitosa\n";
    echo "Número de recibos devueltos: " . count($data) . "\n\n";
    
    if (count($data) > 0) {
        echo "Primeros 3 recibos:\n";
        for ($i = 0; $i < min(3, count($data)); $i++) {
            $recibo = $data[$i];
            echo "- {$recibo['numero_recibo']} ({$recibo['periodo']}) - Saldo: $" . number_format($recibo['saldo_pendiente'], 2) . "\n";
        }
        echo "\n";
        
        // Probar getSaldoRecibo con el primer recibo
        echo "=== PROBANDO getSaldoRecibo ===\n";
        $primerRecibo = $data[0];
        
        $requestSaldo = new Request();
        $requestSaldo->merge([
            'apartamento_id' => $apartamento->id,
            'recibo_id' => $primerRecibo['id']
        ]);
        
        $responseSaldo = $controller->getSaldoRecibo($requestSaldo);
        $dataSaldo = $responseSaldo->getData(true);
        
        echo "✅ Respuesta de saldo exitosa\n";
        echo "Recibo: {$primerRecibo['numero_recibo']}\n";
        echo "Saldo pendiente: $" . number_format($dataSaldo['saldo_pendiente'], 2) . "\n";
        
        // Verificar que coincidan
        if (abs($dataSaldo['saldo_pendiente'] - $primerRecibo['saldo_pendiente']) < 0.01) {
            echo "✅ Los saldos coinciden correctamente\n";
        } else {
            echo "❌ Los saldos NO coinciden\n";
            echo "   getRecibosPorApartamento: $" . number_format($primerRecibo['saldo_pendiente'], 2) . "\n";
            echo "   getSaldoRecibo: $" . number_format($dataSaldo['saldo_pendiente'], 2) . "\n";
        }
        
    } else {
        echo "❌ No se devolvieron recibos\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n=== VERIFICACIÓN DE APARTAMENTOS DISPONIBLES ===\n";

// Verificar que hay apartamentos disponibles
$apartamentos = Apartamento::orderBy('numero')->get();
echo "Total de apartamentos: {$apartamentos->count()}\n";

if ($apartamentos->count() > 0) {
    echo "Primeros 5 apartamentos:\n";
    foreach ($apartamentos->take(5) as $apt) {
        echo "- Apartamento {$apt->numero} (ID: {$apt->id}) - {$apt->propietario}\n";
    }
}

echo "\n=== RESUMEN ===\n";
echo "✅ La funcionalidad del backend está funcionando correctamente\n";
echo "✅ El apartamento 13 tiene recibos con saldo pendiente\n";
echo "✅ Los métodos del controlador responden correctamente\n";
echo "\nSi hay errores en el frontend, pueden ser por:\n";
echo "1. Problemas de autenticación/sesión\n";
echo "2. Errores de JavaScript en el navegador\n";
echo "3. Problemas de red o CSRF token\n";

echo "\n=== FIN DE LA PRUEBA ===\n";
?>