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
     * El saldo pendiente es la suma total de todos los recibos asignados menos los pagos confirmados
     */
    public function getSaldoPendienteAttribute()
    {
        $totalSaldo = 0;
        
        // Obtener todos los recibos realmente asignados (excluyendo rechazados)
        $recibosAsignados = \App\Models\ReciboGastoComun::whereHas('pagos', function($query) {
            $query->where('apartamento_id', $this->id)
                  ->where('estado', '!=', 'rechazado');
        })->get();
        
        // Calcular el saldo pendiente para cada recibo
        foreach ($recibosAsignados as $recibo) {
            // Obtener el total pagado para este recibo específico
            $totalPagado = $this->pagos()
                ->where('recibo_gasto_comun_id', $recibo->id)
                ->where('estado', 'confirmado')
                ->sum('monto_pagado');
            
            // Calcular saldo pendiente del recibo (no puede ser negativo)
            $saldoRecibo = max(0, $recibo->total_recibo - $totalPagado);
            $totalSaldo += $saldoRecibo;
        }
        
        return $totalSaldo;
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
      * Actualizar el estatus financiero basado en saldo pendiente y recibos con deuda real
      * - Solvente: sin recibos con saldo pendiente
      * - Deudor: 1 a 3 recibos con saldo pendiente
      * - Moroso: más de 3 recibos con saldo pendiente
      */
    public function actualizarEstatusFinanciero()
    {
        $recibosConDeuda = 0;
        $saldoPendienteTotal = 0;
        
        // Obtener todos los recibos asignados (excluyendo rechazados)
        $recibosAsignados = ReciboGastoComun::whereHas('pagos', function($query) {
            $query->where('apartamento_id', $this->id)
                  ->where('estado', '!=', 'rechazado');
        })->get();
        
        foreach ($recibosAsignados as $recibo) {
            // Solo considerar recibos con monto > 0 (ignorar datos corruptos)
            if ($recibo->total_recibo <= 0) {
                continue;
            }
            
            $totalPagado = $this->pagos()
                ->where('recibo_gasto_comun_id', $recibo->id)
                ->where('estado', 'confirmado')
                ->sum('monto_pagado');
            
            $saldoRecibo = max(0, $recibo->total_recibo - $totalPagado);
            
            if ($saldoRecibo > 0) {
                $recibosConDeuda++;
                $saldoPendienteTotal += $saldoRecibo;
            }
        }
        
        // Determinar estatus basado en recibos con deuda real
         if ($saldoPendienteTotal <= 0) {
             $nuevoEstatus = 'solvente';
         } elseif ($recibosConDeuda >= 1 && $recibosConDeuda <= 3) {
             $nuevoEstatus = 'deudor';
         } else {
             $nuevoEstatus = 'moroso';
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
