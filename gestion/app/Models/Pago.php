<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $fillable = [
        'apartamento_id',
        'recibo_gasto_comun_id',
        'monto_pagado',
        'monto_en_bs',
        'fecha_pago',
        'metodo_pago',
        'numero_comprobante',
        'observaciones',
        'estado'
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'monto_pagado' => 'decimal:2',
        'monto_en_bs' => 'decimal:2'
    ];

    /**
     * Relación con apartamento
     */
    public function apartamento()
    {
        return $this->belongsTo(Apartamento::class);
    }

    /**
     * Relación con recibo de gasto común
     */
    public function reciboGastoComun()
    {
        return $this->belongsTo(ReciboGastoComun::class);
    }

    /**
     * Verificar si el pago está confirmado
     */
    public function estaConfirmado()
    {
        return $this->estado === 'confirmado';
    }

    /**
     * Obtener el método de pago formateado
     */
    public function getMetodoPagoFormateadoAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->metodo_pago));
    }
}
