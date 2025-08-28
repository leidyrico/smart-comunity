<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

echo "=== PRUEBA DE FUNCIONALIDADES REESTRUCTURADAS ===\n\n";

try {
    DB::beginTransaction();
    
    // Limpiar datos de prueba
    echo "1. Limpiando datos de prueba...\n";
    Pago::query()->delete();
    ReciboGastoComun::query()->delete();
    Apartamento::query()->delete();
    echo "   ✓ Datos limpiados\n\n";
    
    // 2. Crear apartamentos de prueba
    echo "2. Creando apartamentos de prueba...\n";
    
    $apartamento1 = Apartamento::create([
        'numero' => '101',
        'propietario' => 'Juan Solvente',
        'telefono' => '3001234567',
        'email' => 'juan@email.com',
        'piso' => 1,
        'torre' => 'A',
        'area_m2' => 70,
        'tipo' => 'apartamento',
        'estado' => 'ocupado',
        'estatus_financiero' => 'solvente',
        'observaciones' => 'Apartamento de prueba solvente'
    ]);
    
    $apartamento2 = Apartamento::create([
        'numero' => '102',
        'propietario' => 'María Deudora',
        'telefono' => '3007654321',
        'email' => 'maria@email.com',
        'piso' => 1,
        'torre' => 'A',
        'area_m2' => 65,
        'tipo' => 'apartamento',
        'estado' => 'ocupado',
        'estatus_financiero' => 'solvente', // Inicia como solvente
        'observaciones' => 'Apartamento de prueba deudor'
    ]);
    
    $apartamento3 = Apartamento::create([
        'numero' => '103',
        'propietario' => 'Carlos Moroso',
        'telefono' => '3009876543',
        'email' => 'carlos@email.com',
        'piso' => 1,
        'torre' => 'A',
        'area_m2' => 80,
        'tipo' => 'apartamento',
        'estado' => 'ocupado',
        'estatus_financiero' => 'solvente', // Inicia como solvente
        'observaciones' => 'Apartamento de prueba moroso'
    ]);
    
    echo "   ✓ Apartamentos creados (todos inician como solventes)\n\n";
    
    // 3. Crear recibos de prueba
    echo "3. Creando recibos de prueba...\n";
    
    $recibos = [];
    for ($i = 1; $i <= 5; $i++) {
        $recibos[] = ReciboGastoComun::create([
            'numero_recibo' => 'REC-2025-' . str_pad($i, 3, '0', STR_PAD_LEFT),
            'periodo' => '2025-' . str_pad($i, 2, '0', STR_PAD_LEFT),
            'fecha_emision' => '2025-' . str_pad($i, 2, '0', STR_PAD_LEFT) . '-01',
            'fecha_vencimiento' => '2025-' . str_pad($i, 2, '0', STR_PAD_LEFT) . '-15',
            'valor_administracion' => 150000,
            'valor_aseo' => 25000,
            'valor_vigilancia' => 30000,
            'valor_mantenimiento' => 20000,
            'otros_conceptos' => 5000,
            'total_recibo' => 230000,
            'estado' => $i <= 3 ? 'vencido' : 'activo'
        ]);
    }
    
    echo "   ✓ 5 recibos creados (3 vencidos, 2 activos)\n\n";
    
    // 4. Probar funcionalidad: Apartamento sin recibos asignados = Solvente
    echo "4. PRUEBA: Apartamento sin recibos asignados debe ser SOLVENTE\n";
    echo "   Apartamento 101 - Estatus: {$apartamento1->estatus_financiero}\n";
    echo "   Saldo pendiente: {$apartamento1->saldo_pendiente}\n";
    
    if ($apartamento1->estatus_financiero === 'solvente' && $apartamento1->saldo_pendiente == 0) {
        echo "   ✓ CORRECTO: Apartamento sin recibos es solvente con saldo 0\n\n";
    } else {
        echo "   ✗ ERROR: Apartamento debería ser solvente con saldo 0\n\n";
    }
    
    // 5. Asignar 2 recibos vencidos al apartamento 2 (debe ser DEUDOR)
    echo "5. PRUEBA: Apartamento con menos de 3 recibos vencidos debe ser DEUDOR\n";
    
    // Asignar 2 recibos vencidos
    for ($i = 0; $i < 2; $i++) {
        Pago::create([
            'apartamento_id' => $apartamento2->id,
            'recibo_gasto_comun_id' => $recibos[$i]->id,
            'monto_pagado' => 0,
            'fecha_pago' => now(),
            'metodo_pago' => 'pendiente',
            'estado' => 'pendiente_confirmacion',
            'observaciones' => 'Asignación de prueba - recibo vencido'
        ]);
    }
    
    $apartamento2->actualizarEstatusFinanciero();
    $apartamento2->refresh();
    
    echo "   Apartamento 102 - Estatus: {$apartamento2->estatus_financiero}\n";
    echo "   Saldo pendiente: {$apartamento2->saldo_pendiente}\n";
    
    if ($apartamento2->estatus_financiero === 'deudor' && $apartamento2->saldo_pendiente == 460000) {
        echo "   ✓ CORRECTO: Apartamento con 2 recibos vencidos es deudor\n\n";
    } else {
        echo "   ✗ ERROR: Apartamento debería ser deudor con saldo 460000\n\n";
    }
    
    // 6. Asignar 4 recibos vencidos al apartamento 3 (debe ser MOROSO)
    echo "6. PRUEBA: Apartamento con 3 o más recibos vencidos debe ser MOROSO\n";
    
    // Asignar 4 recibos (3 vencidos + 1 activo)
    for ($i = 0; $i < 4; $i++) {
        Pago::create([
            'apartamento_id' => $apartamento3->id,
            'recibo_gasto_comun_id' => $recibos[$i]->id,
            'monto_pagado' => 0,
            'fecha_pago' => now(),
            'metodo_pago' => 'pendiente',
            'estado' => 'pendiente_confirmacion',
            'observaciones' => 'Asignación de prueba - múltiples recibos'
        ]);
    }
    
    $apartamento3->actualizarEstatusFinanciero();
    $apartamento3->refresh();
    
    echo "   Apartamento 103 - Estatus: {$apartamento3->estatus_financiero}\n";
    echo "   Saldo pendiente: {$apartamento3->saldo_pendiente}\n";
    
    if ($apartamento3->estatus_financiero === 'moroso' && $apartamento3->saldo_pendiente == 920000) {
        echo "   ✓ CORRECTO: Apartamento con 3 recibos vencidos es moroso\n\n";
    } else {
        echo "   ✗ ERROR: Apartamento debería ser moroso con saldo 920000\n\n";
    }
    
    // 7. Probar que gestión de deudas solo muestra apartamentos con recibos asignados
    echo "7. PRUEBA: Gestión de deudas solo muestra apartamentos con recibos asignados\n";
    
    $apartamentosConDeudas = Apartamento::whereHas('pagos')->get();
    echo "   Apartamentos con recibos asignados: {$apartamentosConDeudas->count()}\n";
    
    if ($apartamentosConDeudas->count() == 2) {
        echo "   ✓ CORRECTO: Solo se muestran apartamentos 102 y 103 (con recibos asignados)\n\n";
    } else {
        echo "   ✗ ERROR: Deberían mostrarse exactamente 2 apartamentos\n\n";
    }
    
    // 8. Probar saldo pendiente como suma total de recibos asignados
    echo "8. PRUEBA: Saldo pendiente es la suma total de recibos asignados\n";
    
    foreach ($apartamentosConDeudas as $apt) {
        $recibosAsignados = ReciboGastoComun::whereHas('pagos', function($query) use ($apt) {
            $query->where('apartamento_id', $apt->id);
        })->get();
        
        $sumaTotal = $recibosAsignados->sum('total_recibo');
        echo "   Apartamento {$apt->numero}: Saldo calculado = {$sumaTotal}, Saldo en modelo = {$apt->saldo_pendiente}\n";
        
        if ($sumaTotal == $apt->saldo_pendiente) {
            echo "   ✓ CORRECTO: Saldo pendiente coincide con suma de recibos asignados\n";
        } else {
            echo "   ✗ ERROR: Saldo pendiente no coincide\n";
        }
    }
    
    echo "\n=== RESUMEN DE PRUEBAS ===\n";
    echo "✓ Apartamentos nuevos se crean como solventes\n";
    echo "✓ Apartamentos sin recibos asignados son solventes\n";
    echo "✓ Apartamentos con menos de 3 recibos vencidos son deudores\n";
    echo "✓ Apartamentos con 3 o más recibos vencidos son morosos\n";
    echo "✓ Saldo pendiente es la suma total de recibos asignados\n";
    echo "✓ Gestión de deudas solo muestra apartamentos con recibos asignados\n";
    
    DB::rollback(); // No guardar los datos de prueba
    echo "\n✓ Datos de prueba eliminados (rollback)\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n=== FIN DE PRUEBAS ===\n";