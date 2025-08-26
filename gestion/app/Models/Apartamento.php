<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'piso',
        'torre',
        'propietario',
        'telefono',
        'email',
        'area_m2',
        'tipo',
        'estado',
        'estatus_financiero',
        'fecha_cambio_estatus',
        'observaciones'
    ];

    protected $casts = [
        'area_m2' => 'decimal:2',
        'piso' => 'integer'
    ];

    /**
     * Relación con recibos de gasto común a través de pagos
     */
    public function recibos()
    {
        return $this->belongsToMany(ReciboGastoComun::class, 'pagos')
                    ->withPivot('monto_pagado', 'fecha_pago', 'estado')
                    ->withTimestamps();
    }

    /**
     * Relación con pagos
     */
    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    /**
     * Obtener el saldo pendiente total
     */
    public function getSaldoPendienteAttribute()
    {
        $totalDeuda = 0;
        
        // Obtener todos los pagos de este apartamento agrupados por recibo
        $pagos = $this->pagos()->with('reciboGastoComun')->get();
        
        // Agrupar pagos por recibo para evitar duplicaciones
        $pagosPorRecibo = $pagos->groupBy('recibo_gasto_comun_id');
        
        foreach ($pagosPorRecibo as $reciboId => $pagosDelRecibo) {
            $recibo = $pagosDelRecibo->first()->reciboGastoComun;
            
            // Determinar qué recibos considerar según el estatus financiero
            $incluirRecibo = false;
            
            if ($this->estatus_financiero === 'solvente') {
                // Para apartamentos solventes: solo recibos activos
                $incluirRecibo = ($recibo->estado === 'activo');
            } elseif ($this->estatus_financiero === 'deudor') {
                // Para apartamentos deudores: verificar si cambiaron hoy
                $cambioHoy = $this->fecha_cambio_estatus && 
                            $this->fecha_cambio_estatus === now()->toDateString();
                
                if ($cambioHoy) {
                    // Deudores que cambiaron hoy: solo recibos activos
                    $incluirRecibo = ($recibo->estado === 'activo');
                } else {
                    // Deudores históricos o primera carga: recibos activos y vencidos
                    $incluirRecibo = in_array($recibo->estado, ['activo', 'vencido']);
                }
            } else {
                // Fallback: recibos activos y vencidos
                $incluirRecibo = in_array($recibo->estado, ['activo', 'vencido']);
            }
            
            if ($incluirRecibo) {
                // Calcular total pagado para este recibo (excluyendo pagos rechazados)
                $totalPagadoRecibo = $pagosDelRecibo
                    ->where('estado', '!=', 'rechazado')
                    ->where('estado', 'confirmado')
                    ->sum('monto_pagado');
                
                // Calcular saldo pendiente para este recibo
                $saldo = $recibo->total_recibo - $totalPagadoRecibo;
                if ($saldo > 0) {
                    $totalDeuda += $saldo;
                }
            }
        }
        
        return $totalDeuda;
    }

    /**
     * Verificar si tiene deudas pendientes
     */
    public function tieneDeudaPendiente()
    {
        return $this->saldo_pendiente > 0;
    }

    /**
     * Obtener el último pago realizado
     */
    public function ultimoPago()
    {
        return $this->pagos()->latest()->first();
    }

    /**
     * Actualizar el estatus financiero basado en deudas pendientes
     */
    public function actualizarEstatusFinanciero()
    {
        // Calcular deuda basada en recibos activos y vencidos
        $recibos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])->get();
        $totalDeuda = 0;
        
        foreach ($recibos as $recibo) {
            // Verificar si existe un pago para este recibo y apartamento
            $pago = $this->pagos()
                ->where('recibo_gasto_comun_id', $recibo->id)
                ->first();
            
            if ($pago) {
                // Si existe pago, calcular saldo basado en monto pagado
                $montoPagado = $pago->estado === 'confirmado' ? $pago->monto_pagado : 0;
                $saldo = $recibo->total_recibo - $montoPagado;
                if ($saldo > 0) {
                    $totalDeuda += $saldo;
                }
            } else {
                // Si no existe pago, toda la deuda está pendiente
                $totalDeuda += $recibo->total_recibo;
            }
        }
        
        $nuevoEstatus = $totalDeuda > 0 ? 'deudor' : 'solvente';
        
        if ($this->estatus_financiero !== $nuevoEstatus) {
            $this->update(['estatus_financiero' => $nuevoEstatus]);
        }
        
        return $nuevoEstatus;
    }

    /**
     * Verificar si es solvente
     */
    public function esSolvente()
    {
        return $this->estatus_financiero === 'solvente';
    }

    /**
     * Verificar si es deudor
     */
    public function esDeudor()
    {
        return $this->estatus_financiero === 'deudor';
    }
}
