<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReciboGastoComun;
use App\Models\Apartamento;
use App\Models\Pago;
use Exception;

class ProbarCreacionRecibo extends Command
{
    protected $signature = 'probar:recibo';
    protected $description = 'Probar la creación de un recibo para identificar errores';

    public function handle()
    {
        try {
            $this->info('Iniciando prueba de creación de recibo...');
            
            // Datos de prueba
            $datos = [
                'numero_recibo' => 'TEST-' . date('YmdHis'),
                'periodo' => date('Y-m'),
                'fecha_emision' => date('Y-m-d'),
                'fecha_vencimiento' => date('Y-m-d', strtotime('+30 days')),
                'valor_administracion' => 100000,
                'valor_aseo' => 20000,
                'valor_vigilancia' => 30000,
                'valor_mantenimiento' => 15000,
                'otros_conceptos' => 5000,
                'estado' => 'activo',
                'observaciones' => 'Recibo de prueba'
            ];
            
            $this->info('Creando recibo con datos: ' . json_encode($datos));
            
            // Crear recibo
            $recibo = new ReciboGastoComun($datos);
            $this->info('Recibo instanciado correctamente');
            
            // Calcular total
            $recibo->calcularTotal();
            $this->info('Total calculado: ' . $recibo->total_recibo);
            
            // Guardar recibo
            $recibo->save();
            $this->info('Recibo guardado con ID: ' . $recibo->id);
            
            // Si es activo, asignar a apartamentos
            if ($recibo->estado === 'activo') {
                $this->info('Asignando recibo a todos los apartamentos...');
                $this->asignarReciboATodosApartamentos($recibo);
                $this->info('Recibo asignado exitosamente');
            }
            
            $this->info('✅ Recibo creado exitosamente!');
            
        } catch (Exception $e) {
            $this->error('❌ Error al crear recibo: ' . $e->getMessage());
            $this->error('Archivo: ' . $e->getFile() . ':' . $e->getLine());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
        
        return 0;
    }
    
    private function asignarReciboATodosApartamentos(ReciboGastoComun $recibo)
    {
        $apartamentos = Apartamento::all();
        $this->info('Apartamentos encontrados: ' . $apartamentos->count());
        
        foreach ($apartamentos as $apartamento) {
            $this->info('Procesando apartamento: ' . $apartamento->numero);
            
            // Determinar el estado del pago según el estatus financiero del apartamento y estado del recibo
            $estadoPago = 'pendiente_confirmacion';
            $observaciones = 'Recibo asignado automáticamente';
            
            // Si el apartamento es solvente y el recibo está vencido, crear el pago como rechazado
            // para que no aparezca en el listado de deudas
            if ($apartamento->estatus_financiero === 'solvente' && $recibo->estado === 'vencido') {
                $estadoPago = 'rechazado';
                $observaciones = 'Recibo asignado automáticamente - Apartamento solvente con recibo vencido';
                $this->info('Apartamento ' . $apartamento->numero . ' - Recibo vencido asignado como rechazado');
            } elseif ($apartamento->estatus_financiero === 'solvente') {
                // Para apartamentos solventes con recibos activos, mantener pendiente
                $observaciones = 'Recibo asignado automáticamente - Apartamento solvente';
            }
            
            // Crear registro de pago
            Pago::create([
                'recibo_gasto_comun_id' => $recibo->id,
                'apartamento_id' => $apartamento->id,
                'monto_pagado' => 0,
                'fecha_pago' => null,
                'metodo_pago' => null,
                'numero_comprobante' => null,
                'observaciones' => $observaciones,
                'estado' => $estadoPago
            ]);
            
            // Solo cambiar a deudor si actualmente es solvente y el pago no fue rechazado
            if ($apartamento->estatus_financiero === 'solvente' && $estadoPago !== 'rechazado') {
                $apartamento->update(['estatus_financiero' => 'deudor']);
            }
            $this->info('Apartamento ' . $apartamento->numero . ' actualizado');
        }
    }
}