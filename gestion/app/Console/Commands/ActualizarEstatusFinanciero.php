<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Apartamento;
use App\Models\ReciboGastoComun;

class ActualizarEstatusFinanciero extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apartamentos:actualizar-estatus 
                            {--apartamento= : ID específico del apartamento a actualizar}
                            {--detallado : Mostrar información detallada del proceso}
                            {--dry-run : Simular la actualización sin guardar cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza el estatus financiero de apartamentos: Moroso (>3 recibos activos/vencidos), Deudor (≤3), Solvente (0)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== ACTUALIZACIÓN ESTATUS FINANCIERO ===');
        $this->newLine();
        
        // Mostrar reglas de negocio
        $this->info('Reglas aplicadas:');
        $this->line('• SOLVENTE: Sin recibos asignados (0 recibos)');
        $this->line('• DEUDOR: 1-3 recibos activos/vencidos asignados');
        $this->line('• MOROSO: Más de 3 recibos activos/vencidos asignados');
        $this->newLine();
        
        $apartamentoId = $this->option('apartamento');
        $detallado = $this->option('detallado');
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('MODO SIMULACIÓN - No se guardarán cambios');
            $this->newLine();
        }
        
        // Obtener apartamentos a procesar
        if ($apartamentoId) {
            $apartamentos = Apartamento::where('id', $apartamentoId)->get();
            if ($apartamentos->isEmpty()) {
                $this->error("No se encontró el apartamento con ID: {$apartamentoId}");
                return 1;
            }
        } else {
            $apartamentos = Apartamento::orderBy('numero')->get();
        }
        
        $this->info("Procesando {$apartamentos->count()} apartamento(s)...");
        $this->newLine();
        
        $contadores = [
            'solvente' => 0,
            'deudor' => 0,
            'moroso' => 0,
            'actualizados' => 0
        ];
        
        $cambios = [];
        
        $this->withProgressBar($apartamentos, function ($apartamento) use (&$contadores, &$cambios, $detallado, $dryRun) {
            $estatusAnterior = $apartamento->estatus_financiero;
            
            // Contar recibos activos o vencidos asignados (incluyendo rechazados)
            $recibosActivosVencidos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])
                ->whereHas('pagos', function($query) use ($apartamento) {
                    $query->where('apartamento_id', $apartamento->id);
                })->count();
            
            // Determinar nuevo estatus según nuevos criterios
            if ($recibosActivosVencidos == 0) {
                $nuevoEstatus = 'solvente';
            } elseif ($recibosActivosVencidos > 3) {
                $nuevoEstatus = 'moroso';
            } else {
                $nuevoEstatus = 'deudor';
            }
            
            // Actualizar si es necesario y no es dry-run
            if ($estatusAnterior !== $nuevoEstatus) {
                if (!$dryRun) {
                    $apartamento->update([
                        'estatus_financiero' => $nuevoEstatus,
                        'fecha_cambio_estatus' => now()->toDateString()
                    ]);
                }
                
                $cambios[] = [
                    'apartamento' => $apartamento->numero,
                    'propietario' => $apartamento->propietario,
                    'anterior' => $estatusAnterior,
                    'nuevo' => $nuevoEstatus,
                    'recibos_activos_vencidos' => $recibosActivosVencidos
                ];
                
                $contadores['actualizados']++;
            }
            
            $contadores[$nuevoEstatus]++;
        });
        
        $this->newLine(2);
        
        // Mostrar resumen
        $this->info('=== RESUMEN DE ACTUALIZACIÓN ===');
        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Total procesados', $apartamentos->count()],
                ['Actualizados', $contadores['actualizados']],
                ['Solventes', $contadores['solvente']],
                ['Deudores', $contadores['deudor']],
                ['Morosos', $contadores['moroso']]
            ]
        );
        
        // Mostrar cambios si los hay
        if (!empty($cambios)) {
            $this->newLine();
            $this->info('=== CAMBIOS REALIZADOS ===');
            
            if ($detallado) {
                $this->table(
                    ['Apartamento', 'Propietario', 'Anterior', 'Nuevo', 'Recibos Activos/Vencidos'],
                    array_map(function($cambio) {
                        return [
                            $cambio['apartamento'],
                            $cambio['propietario'],
                            $cambio['anterior'],
                            $cambio['nuevo'],
                            $cambio['recibos_activos_vencidos']
                        ];
                    }, $cambios)
                );
            } else {
                foreach ($cambios as $cambio) {
                    $this->line("Apartamento {$cambio['apartamento']} ({$cambio['propietario']}): {$cambio['anterior']} → {$cambio['nuevo']}");
                }
            }
        } else {
            $this->info('No se realizaron cambios.');
        }
        
        $this->newLine();
        $this->info('✅ Proceso completado exitosamente.');
        
        return 0;
    }
}
