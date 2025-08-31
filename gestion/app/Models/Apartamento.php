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
     * Actualizar el estatus financiero basado en recibos asignados
     * - Solvente: sin recibos asignados (0 recibos)
     * - Deudor: 1-3 recibos activos/vencidos asignados
     * - Moroso: más de 3 recibos activos/vencidos asignados
     */
    public function actualizarEstatusFinanciero()
    {
        // Contar recibos activos o vencidos asignados (incluyendo rechazados)
        $recibosActivosVencidos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])
            ->whereHas('pagos', function($query) {
                $query->where('apartamento_id', $this->id);
            })->count();
        
        // Determinar nuevo estatus según nuevos criterios
        if ($recibosActivosVencidos == 0) {
            $nuevoEstatus = 'solvente';
        } elseif ($recibosActivosVencidos > 3) {
            $nuevoEstatus = 'moroso';
        } else {
            $nuevoEstatus = 'deudor';
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
