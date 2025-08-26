<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

class CorregirDatos extends Command
{
    protected $signature = 'datos:corregir';
    protected $description = 'Corregir datos inconsistentes en la base de datos';

    public function handle()
    {
        $this->info('Iniciando corrección de datos...');
        
        // 1. Corregir estados de recibos inválidos
        $this->info('\n1. Corrigiendo estados de recibos...');
        $recibosInvalidos = ReciboGastoComun::whereNotIn('estado', ['activo', 'vencido', 'anulado'])->get();
        
        foreach ($recibosInvalidos as $recibo) {
            $estadoOriginal = $recibo->estado;
            
            // Determinar el estado correcto basado en fechas
            if (now()->gt($recibo->fecha_vencimiento)) {
                $recibo->estado = 'vencido';
            } else {
                $recibo->estado = 'activo';
            }
            
            $recibo->save();
            $this->info("Recibo {$recibo->numero_recibo}: '{$estadoOriginal}' -> '{$recibo->estado}'");
        }
        
        // 2. Verificar apartamentos faltantes
        $this->info('\n2. Verificando apartamentos faltantes...');
        $totalApartamentos = Apartamento::count();
        $totalRecibos = ReciboGastoComun::count();
        $totalPagos = Pago::count();
        
        $this->info("Total apartamentos: {$totalApartamentos}");
        $this->info("Total recibos: {$totalRecibos}");
        $this->info("Total pagos: {$totalPagos}");
        
        // 3. Buscar números de apartamento en pagos que no existen
        $this->info('\n3. Buscando apartamentos referenciados en pagos...');
        $apartamentosEnPagos = DB::table('pagos')
            ->join('apartamentos', 'pagos.apartamento_id', '=', 'apartamentos.id')
            ->select('apartamentos.numero')
            ->distinct()
            ->pluck('numero')
            ->toArray();
            
        $this->info('Apartamentos encontrados en pagos: ' . implode(', ', $apartamentosEnPagos));
        
        // 4. Si no hay apartamentos, crear algunos de ejemplo
        if ($totalApartamentos == 0) {
            $this->info('\n4. No hay apartamentos. Creando apartamentos de ejemplo...');
            
            $apartamentosEjemplo = [
                ['numero' => '101', 'propietario' => 'Juan Pérez'],
                ['numero' => '102', 'propietario' => 'María González'],
                ['numero' => '103', 'propietario' => 'Carlos Rodríguez'],
                ['numero' => '201', 'propietario' => 'Ana López'],
                ['numero' => '202', 'propietario' => 'Luis Martínez']
            ];
            
            foreach ($apartamentosEjemplo as $apt) {
                Apartamento::create([
                    'numero' => $apt['numero'],
                    'propietario' => $apt['propietario'],
                    'telefono' => '',
                    'email' => '',
                    'estatus_financiero' => 'solvente'
                ]);
                $this->info("Apartamento {$apt['numero']} creado");
            }
        }
        
        // 5. Recalcular estatus financiero de todos los apartamentos
        $this->info('\n5. Recalculando estatus financiero...');
        $apartamentos = Apartamento::all();
        foreach ($apartamentos as $apartamento) {
            $apartamento->actualizarEstatusFinanciero();
            $this->info("Apartamento {$apartamento->numero}: {$apartamento->estatus_financiero} (Saldo: {$apartamento->saldo_pendiente})");
        }
        
        $this->info('\n✅ Corrección de datos completada.');
        return 0;
    }
}