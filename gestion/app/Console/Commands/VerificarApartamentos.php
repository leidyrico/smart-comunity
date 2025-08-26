<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Apartamento;

class VerificarApartamentos extends Command
{
    protected $signature = 'verificar:apartamentos';
    protected $description = 'Verificar si existen apartamentos en la base de datos';

    public function handle()
    {
        $count = Apartamento::count();
        $this->info("Total de apartamentos: {$count}");
        
        if ($count > 0) {
            $apartamentos = Apartamento::select('id', 'numero', 'estatus_financiero')->get();
            $this->table(['ID', 'Número', 'Estatus Financiero'], $apartamentos->toArray());
        } else {
            $this->warn('No hay apartamentos registrados en la base de datos.');
            $this->info('Esto podría causar errores al crear recibos activos.');
        }
        
        return 0;
    }
}