<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;

class RevisarDatosCompletos extends Command
{
    protected $signature = 'datos:revisar';
    protected $description = 'Revisar todos los datos del sistema';

    public function handle()
    {
        $this->info('=== REVISIÓN COMPLETA DE DATOS ===');
        
        // Contar registros
        $totalApartamentos = Apartamento::count();
        $totalRecibos = ReciboGastoComun::count();
        $totalPagos = Pago::count();
        
        $this->info("Total apartamentos: {$totalApartamentos}");
        $this->info("Total recibos: {$totalRecibos}");
        $this->info("Total pagos: {$totalPagos}");
        
        // Revisar apartamentos
        $this->info('\n=== APARTAMENTOS ===');
        $apartamentos = Apartamento::all();
        foreach ($apartamentos as $apt) {
            $this->info("Apartamento {$apt->numero}: {$apt->estatus_financiero}, Saldo: {$apt->saldo_pendiente}");
        }
        
        // Revisar recibos
        $this->info('\n=== RECIBOS ===');
        $recibos = ReciboGastoComun::orderBy('id')->get();
        foreach ($recibos as $recibo) {
            $this->info("Recibo {$recibo->id}: {$recibo->estado}, Total: {$recibo->total_recibo}");
        }
        
        // Revisar pagos
        $this->info('\n=== PAGOS ===');
        $pagos = Pago::with(['apartamento', 'reciboGastoComun'])->get();
        foreach ($pagos as $pago) {
            $apartamentoNum = $pago->apartamento ? $pago->apartamento->numero : 'N/A';
            $reciboId = $pago->recibo_gasto_comun_id ?? 'N/A';
            $this->info("Pago ID {$pago->id}: Apt {$apartamentoNum}, Recibo {$reciboId}, Monto: {$pago->monto_pagado}, Estado: {$pago->estado}");
        }
        
        // Verificar si faltan apartamentos
        $this->info('\n=== ANÁLISIS ===');
        if ($totalApartamentos == 1 && $totalRecibos == 20) {
            $this->warn('¡PROBLEMA DETECTADO! Solo hay 1 apartamento pero 20 recibos.');
            $this->warn('Esto sugiere que faltan apartamentos en la base de datos.');
            $this->warn('Posibles causas:');
            $this->warn('1. Error en la importación de Excel');
            $this->warn('2. Apartamentos no fueron creados correctamente');
            $this->warn('3. Datos de prueba incompletos');
        }
        
        return 0;
    }
}