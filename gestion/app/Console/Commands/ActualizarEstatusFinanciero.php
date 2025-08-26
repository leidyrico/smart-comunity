<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Apartamento;

class ActualizarEstatusFinanciero extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apartamentos:actualizar-estatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza el estatus financiero de todos los apartamentos basado en sus deudas pendientes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando actualización del estatus financiero de apartamentos...');
        
        $apartamentos = Apartamento::all();
        $actualizados = 0;
        $solventes = 0;
        $deudores = 0;
        
        $this->withProgressBar($apartamentos, function ($apartamento) use (&$actualizados, &$solventes, &$deudores) {
            $estatusAnterior = $apartamento->estatus_financiero;
            $nuevoEstatus = $apartamento->actualizarEstatusFinanciero();
            
            if ($estatusAnterior !== $nuevoEstatus) {
                $actualizados++;
            }
            
            if ($nuevoEstatus === 'solvente') {
                $solventes++;
            } else {
                $deudores++;
            }
        });
        
        $this->newLine(2);
        $this->info("Actualización completada:");
        $this->line("- Total de apartamentos: {$apartamentos->count()}");
        $this->line("- Apartamentos actualizados: {$actualizados}");
        $this->line("- Apartamentos solventes: {$solventes}");
        $this->line("- Apartamentos deudores: {$deudores}");
        
        return Command::SUCCESS;
    }
}
