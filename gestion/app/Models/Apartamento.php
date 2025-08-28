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
     * El saldo pendiente es la suma total de todos los recibos asignados al apartamento
     */
    public function getSaldoPendienteAttribute()
    {
        $totalSaldo = 0;
        
        // Obtener todos los recibos realmente asignados (excluyendo rechazados)
        $recibosAsignados = \App\Models\ReciboGastoComun::whereHas('pagos', function($query) {
            $query->where('apartamento_id', $this->id)
                  ->where('estado', '!=', 'rechazado');
        })->get();
        
        // Sumar el total de todos los recibos asignados
        foreach ($recibosAsignados as $recibo) {
            $totalSaldo += $recibo->total_recibo;
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
     * Actualizar el estatus financiero basado en recibos asignados
     * - Solvente: sin recibos asignados
     * - Deudor: menos de 3 recibos vencidos asignados
     * - Moroso: 3 o más recibos vencidos asignados
     */
    public function actualizarEstatusFinanciero()
    {
        // Contar recibos realmente asignados (excluyendo rechazados)
        $recibosAsignados = $this->pagos()
            ->where('estado', '!=', 'rechazado')
            ->distinct('recibo_gasto_comun_id')
            ->count();
        
        // Si no tiene recibos asignados, es solvente
        if ($recibosAsignados == 0) {
            $nuevoEstatus = 'solvente';
        } else {
            // Contar recibos vencidos asignados (excluyendo rechazados)
            $recibosVencidosAsignados = ReciboGastoComun::where('estado', 'vencido')
                ->whereHas('pagos', function($query) {
                    $query->where('apartamento_id', $this->id)
                          ->where('estado', '!=', 'rechazado');
                })->count();
            
            // Determinar estatus basado en número de recibos vencidos
            if ($recibosVencidosAsignados >= 3) {
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
