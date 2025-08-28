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
        
        // Para apartamentos solventes: no tienen saldo pendiente
        if ($this->estatus_financiero === 'solvente') {
            return 0;
        }
        
        // Para apartamentos deudores y morosos: calcular saldo basado en recibos asignados (con pagos)
        // Solo considerar recibos que tienen pagos asociados a este apartamento
        $recibosAsignados = \App\Models\ReciboGastoComun::whereHas('pagos', function($query) {
            $query->where('apartamento_id', $this->id);
        })->get();
        
        foreach ($recibosAsignados as $recibo) {
            // Calcular total pagado para este recibo por este apartamento
            $totalPagadoRecibo = $this->pagos()
                ->where('recibo_gasto_comun_id', $recibo->id)
                ->where('estado', 'confirmado')
                ->sum('monto_pagado');
            
            // Calcular saldo pendiente para este recibo
            $saldo = $recibo->total_recibo - $totalPagadoRecibo;
            if ($saldo > 0) {
                $totalDeuda += $saldo;
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
        $recibosVencidosPendientes = 0;
        
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
                    // Contar recibos vencidos con saldo pendiente
                    if ($recibo->estado === 'vencido') {
                        $recibosVencidosPendientes++;
                    }
                }
            } else {
                // Si no existe pago, toda la deuda está pendiente
                $totalDeuda += $recibo->total_recibo;
                // Contar recibos vencidos sin pago
                if ($recibo->estado === 'vencido') {
                    $recibosVencidosPendientes++;
                }
            }
        }
        
        // Determinar estatus basado en deuda y número de recibos vencidos
        $nuevoEstatus = 'solvente';
        if ($totalDeuda > 0) {
            if ($recibosVencidosPendientes > 3) {
                $nuevoEstatus = 'moroso';
            } else {
                $nuevoEstatus = 'deudor';
            }
        }
        
        if ($this->estatus_financiero !== $nuevoEstatus) {
            $this->update([
                'estatus_financiero' => $nuevoEstatus,
                'fecha_cambio_estatus' => now()->toDateString()
            ]);
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

    /**
     * Verificar si es moroso
     */
    public function esMoroso()
    {
        return $this->estatus_financiero === 'moroso';
    }

    /**
     * Verificar si tiene deudas (deudor o moroso)
     */
    public function tieneDeudas()
    {
        return in_array($this->estatus_financiero, ['deudor', 'moroso']);
    }
}
