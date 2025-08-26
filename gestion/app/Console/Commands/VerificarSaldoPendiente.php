<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReciboGastoComun;
use App\Models\Apartamento;
use App\Models\Pago;

class VerificarSaldoPendiente extends Command
{
    protected $signature = 'verificar:saldo-pendiente';
    protected $description = 'Verificar por qué los recibos activos no se reflejan en el saldo pendiente';

    public function handle()
    {
        $this->info('=== VERIFICACIÓN DE SALDO PENDIENTE ===');
        
        // 1. Verificar recibos activos
        $this->info('\n1. RECIBOS ACTIVOS:');
        $recibosActivos = ReciboGastoComun::where('estado', 'activo')->get();
        
        if ($recibosActivos->isEmpty()) {
            $this->warn('No hay recibos activos en el sistema.');
            return;
        }
        
        foreach ($recibosActivos as $recibo) {
            $this->info("- ID: {$recibo->id}, Número: {$recibo->numero_recibo}, Período: {$recibo->periodo}, Total: $" . number_format($recibo->total_recibo, 0));
        }
        
        // 2. Verificar pagos para recibos activos
        $this->info('\n2. PAGOS PARA RECIBOS ACTIVOS:');
        $recibosActivosIds = $recibosActivos->pluck('id');
        $pagos = Pago::whereIn('recibo_gasto_comun_id', $recibosActivosIds)->get();
        
        if ($pagos->isEmpty()) {
            $this->warn('No hay pagos registrados para los recibos activos.');
        } else {
            foreach ($pagos as $pago) {
                $this->info("- Apartamento: {$pago->apartamento_id}, Recibo: {$pago->recibo_gasto_comun_id}, Monto: $" . number_format($pago->monto_pagado, 0) . ", Estado: {$pago->estado}");
            }
        }
        
        // 3. Verificar saldo pendiente de algunos apartamentos
        $this->info('\n3. SALDO PENDIENTE DE APARTAMENTOS:');
        $apartamentos = Apartamento::take(5)->get();
        
        foreach ($apartamentos as $apartamento) {
            $saldoPendiente = $apartamento->saldo_pendiente;
            $estatusFinanciero = $apartamento->estatus_financiero;
            $this->info("- Apartamento {$apartamento->numero}: Saldo pendiente: $" . number_format($saldoPendiente, 0) . ", Estatus: {$estatusFinanciero}");
            
            // Mostrar cálculo detallado para el primer apartamento
            if ($apartamento->id === $apartamentos->first()->id) {
                $this->info('  Cálculo detallado:');
                foreach ($recibosActivos as $recibo) {
                    $montoPagado = $apartamento->pagos()
                        ->where('recibo_gasto_comun_id', $recibo->id)
                        ->where('estado', 'confirmado')
                        ->sum('monto_pagado');
                    $saldo = $recibo->total_recibo - $montoPagado;
                    $this->info("    Recibo {$recibo->numero_recibo}: Total $" . number_format($recibo->total_recibo, 0) . " - Pagado $" . number_format($montoPagado, 0) . " = Saldo $" . number_format($saldo, 0));
                }
            }
        }
        
        // 4. Verificar método getSaldoPendienteAttribute
        $this->info('\n4. VERIFICACIÓN DEL MÉTODO getSaldoPendienteAttribute:');
        $primerApartamento = $apartamentos->first();
        if ($primerApartamento) {
            $this->info("Apartamento {$primerApartamento->numero}:");
            $this->info("- Estatus financiero: {$primerApartamento->estatus_financiero}");
            
            if ($primerApartamento->estatus_financiero === 'solvente') {
                $this->info('- El apartamento es solvente, por eso el saldo pendiente es 0');
            } else {
                $this->info('- El apartamento es deudor, calculando saldo...');
                $recibos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])->get();
                $totalDeuda = 0;
                
                foreach ($recibos as $recibo) {
                    $montoPagado = $primerApartamento->pagos()
                        ->where('recibo_gasto_comun_id', $recibo->id)
                        ->where('estado', 'confirmado')
                        ->sum('monto_pagado');
                    $saldo = $recibo->total_recibo - $montoPagado;
                    if ($saldo > 0) {
                        $totalDeuda += $saldo;
                        $this->info("  - Recibo {$recibo->numero_recibo} ({$recibo->estado}): Saldo $" . number_format($saldo, 0));
                    }
                }
                $this->info("- Total deuda calculada: $" . number_format($totalDeuda, 0));
            }
        }
        
        $this->info('\n=== FIN DE VERIFICACIÓN ===');
    }
}