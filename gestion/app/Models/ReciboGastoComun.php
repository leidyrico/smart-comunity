<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ReciboGastoComun extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_recibo',
        'periodo',
        'fecha_emision',
        'fecha_vencimiento',
        'valor_administracion',
        'valor_aseo',
        'valor_vigilancia',
        'valor_mantenimiento',
        'otros_conceptos',
        'total_recibo',
        'observaciones',
        'archivo_adjunto',
        'estado'
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'valor_administracion' => 'decimal:2',
        'valor_aseo' => 'decimal:2',
        'valor_vigilancia' => 'decimal:2',
        'valor_mantenimiento' => 'decimal:2',
        'otros_conceptos' => 'decimal:2',
        'total_recibo' => 'decimal:2'
    ];

    /**
     * Relación con pagos
     */
    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    /**
     * Relación con apartamentos a través de pagos
     */
    public function apartamentos()
    {
        return $this->belongsToMany(Apartamento::class, 'pagos')
                    ->withPivot('monto_pagado', 'fecha_pago', 'estado')
                    ->withTimestamps();
    }

    /**
     * Calcular el total del recibo automáticamente
     */
    public function calcularTotal()
    {
        $this->total_recibo = $this->valor_administracion + 
                             $this->valor_aseo + 
                             $this->valor_vigilancia + 
                             $this->valor_mantenimiento + 
                             $this->otros_conceptos;
        return $this->total_recibo;
    }

    /**
     * Verificar si el recibo está vencido
     */
    public function estaVencido()
    {
        return Carbon::now()->gt($this->fecha_vencimiento) && $this->estado === 'activo';
    }

    /**
     * Obtener el monto total pagado para este recibo
     */
    public function getTotalPagadoAttribute()
    {
        return $this->pagos()->sum('monto_pagado');
    }

    /**
     * Obtener el saldo pendiente del recibo
     */
    public function getSaldoPendienteAttribute()
    {
        return $this->total_recibo - $this->total_pagado;
    }

    /**
     * Verificar si el recibo está completamente pagado
     */
    public function estaPagado()
    {
        return $this->saldo_pendiente <= 0;
    }
}
