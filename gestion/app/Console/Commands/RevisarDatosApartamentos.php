<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Apartamento;
use App\Models\ReciboGastoComun;

class RevisarDatosApartamentos extends Command
{
    protected $signature = 'apartamentos:revisar';
    protected $description = 'Revisar datos de apartamentos para identificar problemas con saldos y estatus financieros';

    public function handle()
    {
        $this->info('=== REVISIÓN DE DATOS DE APARTAMENTOS ===');
        
        $apartamentos = Apartamento::all();
        $problemasEncontrados = 0;
        
        foreach ($apartamentos as $apartamento) {
            $this->info("\n--- Apartamento {$apartamento->numero} ---");
            $this->info("Estatus Financiero: {$apartamento->estatus_financiero}");
            $this->info("Saldo Pendiente: {$apartamento->saldo_pendiente}");
            
            // Obtener recibos activos y vencidos
            $recibosActivos = ReciboGastoComun::where('estado', 'activo')->get();
            $recibosVencidos = ReciboGastoComun::where('estado', 'vencido')->get();
            
            $this->info("Recibos activos: {$recibosActivos->count()}");
            $this->info("Recibos vencidos: {$recibosVencidos->count()}");
            
            // Revisar pagos del apartamento
            $pagos = $apartamento->pagos()->with('reciboGastoComun')->get();
            $this->info("Total pagos registrados: {$pagos->count()}");
            
            $totalDeudaCalculada = 0;
            foreach ($recibosActivos->merge($recibosVencidos) as $recibo) {
                $pago = $pagos->where('recibo_gasto_comun_id', $recibo->id)->first();
                if ($pago) {
                    $montoPagado = $pago->estado === 'confirmado' ? $pago->monto_pagado : 0;
                    $saldo = $recibo->total_recibo - $montoPagado;
                    if ($saldo > 0) {
                        $totalDeudaCalculada += $saldo;
                        $this->info("  Recibo {$recibo->id} ({$recibo->estado}): Saldo pendiente {$saldo}");
                    }
                } else {
                    $totalDeudaCalculada += $recibo->total_recibo;
                    $this->info("  Recibo {$recibo->id} ({$recibo->estado}): Sin pago registrado, deuda completa {$recibo->total_recibo}");
                }
            }
            
            $this->info("Total deuda calculada manualmente: {$totalDeudaCalculada}");
            
            // Verificar discrepancias
            $tieneProblemas = false;
            if ($apartamento->saldo_pendiente != $totalDeudaCalculada) {
                $this->error("¡DISCREPANCIA SALDO! Saldo en modelo: {$apartamento->saldo_pendiente}, Calculado: {$totalDeudaCalculada}");
                $tieneProblemas = true;
            }
            
            $estatusEsperado = $totalDeudaCalculada > 0 ? 'deudor' : 'solvente';
            if ($apartamento->estatus_financiero !== $estatusEsperado) {
                $this->error("¡ESTATUS INCORRECTO! Actual: {$apartamento->estatus_financiero}, Esperado: {$estatusEsperado}");
                $tieneProblemas = true;
            }
            
            if ($tieneProblemas) {
                $problemasEncontrados++;
            } else {
                // Solo mostrar apartamentos sin problemas de forma resumida
                $this->line("Apartamento {$apartamento->numero}: OK (Estatus: {$apartamento->estatus_financiero}, Saldo: {$apartamento->saldo_pendiente})");
            }
        }
        
        $this->info("\n=== RESUMEN ===\nApartamentos con problemas: {$problemasEncontrados}\nTotal apartamentos: {$apartamentos->count()}");
        return 0;
    }
}