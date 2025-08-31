<?php

require_once 'vendor/autoload.php';

// Configurar la aplicación Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

echo "=== VERIFICACIÓN DIRECTA EN BASE DE DATOS ===\n\n";

// Verificar apartamento 91
echo "=== VERIFICANDO APARTAMENTO 91 ===\n";
$apartamento = Apartamento::where('numero', '91')->first();
if (!$apartamento) {
    echo "❌ No se encontró el apartamento 91\n";
    exit;
}

echo "✅ Apartamento encontrado:\n";
echo "   ID: {$apartamento->id}\n";
echo "   Número: {$apartamento->numero}\n";
echo "   Propietario: {$apartamento->propietario}\n\n";

// Consulta SQL directa para buscar pagos
echo "=== CONSULTA SQL DIRECTA ===\n";
$pagosSQL = DB::select("SELECT * FROM pagos WHERE apartamento_id = ?", [$apartamento->id]);

echo "Total de pagos encontrados con SQL directo: " . count($pagosSQL) . "\n\n";

if (count($pagosSQL) > 0) {
    foreach ($pagosSQL as $pago) {
        echo "📋 Pago ID: {$pago->id}\n";
        echo "   💰 Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
        echo "   📅 Fecha: {$pago->fecha_pago}\n";
        echo "   🔄 Estado: {$pago->estado}\n";
        echo "   📄 Comprobante: {$pago->numero_comprobante}\n";
        echo "   📝 Observaciones: {$pago->observaciones}\n";
        echo "   📋 Recibo ID: {$pago->recibo_gasto_comun_id}\n";
        echo "   📅 Creado: {$pago->created_at}\n";
        echo "   " . str_repeat("-", 50) . "\n";
    }
}

// Buscar todos los apartamentos que tienen pagos
echo "\n=== APARTAMENTOS CON PAGOS ===\n";
$apartamentosConPagos = DB::select("SELECT DISTINCT apartamento_id, COUNT(*) as total_pagos FROM pagos GROUP BY apartamento_id ORDER BY apartamento_id");

echo "Total de apartamentos con pagos: " . count($apartamentosConPagos) . "\n\n";

foreach ($apartamentosConPagos as $apt) {
    $apartamentoInfo = Apartamento::find($apt->apartamento_id);
    $numeroApt = $apartamentoInfo ? $apartamentoInfo->numero : 'N/A';
    echo "🏠 Apartamento ID: {$apt->apartamento_id} (Número: {$numeroApt}) - Total pagos: {$apt->total_pagos}\n";
}

// Buscar pagos de $100 en toda la base de datos
echo "\n=== PAGOS DE $100 EN TODA LA BASE DE DATOS ===\n";
$pagos100Global = DB::select("SELECT p.*, a.numero as apartamento_numero FROM pagos p LEFT JOIN apartamentos a ON p.apartamento_id = a.id WHERE p.monto_pagado = 100.00 ORDER BY p.created_at DESC");

echo "Total de pagos de $100 encontrados: " . count($pagos100Global) . "\n\n";

if (count($pagos100Global) > 0) {
    foreach ($pagos100Global as $pago) {
        echo "📋 Pago ID: {$pago->id} - Apartamento: {$pago->apartamento_numero} (ID: {$pago->apartamento_id})\n";
        echo "   💰 Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
        echo "   📅 Fecha: {$pago->fecha_pago}\n";
        echo "   🔄 Estado: {$pago->estado}\n";
        echo "   📄 Comprobante: {$pago->numero_comprobante}\n";
        echo "   📝 Observaciones: {$pago->observaciones}\n";
        echo "   📅 Creado: {$pago->created_at}\n";
        echo "   " . str_repeat("-", 50) . "\n";
    }
}

// Buscar pagos globales en toda la base de datos
echo "\n=== PAGOS GLOBALES EN TODA LA BASE DE DATOS ===\n";
$pagosGlobalesGlobal = DB::select("SELECT p.*, a.numero as apartamento_numero FROM pagos p LEFT JOIN apartamentos a ON p.apartamento_id = a.id WHERE p.recibo_gasto_comun_id IS NULL ORDER BY p.created_at DESC");

echo "Total de pagos globales encontrados: " . count($pagosGlobalesGlobal) . "\n\n";

if (count($pagosGlobalesGlobal) > 0) {
    foreach ($pagosGlobalesGlobal as $pago) {
        echo "📋 Pago Global ID: {$pago->id} - Apartamento: {$pago->apartamento_numero} (ID: {$pago->apartamento_id})\n";
        echo "   💰 Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
        echo "   📅 Fecha: {$pago->fecha_pago}\n";
        echo "   🔄 Estado: {$pago->estado}\n";
        echo "   📄 Comprobante: {$pago->numero_comprobante}\n";
        echo "   📝 Observaciones: {$pago->observaciones}\n";
        echo "   📅 Creado: {$pago->created_at}\n";
        echo "   " . str_repeat("-", 50) . "\n";
    }
}

echo "\n=== VERIFICACIÓN COMPLETADA ===\n";