<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Inquilino extends Model
{
    use HasFactory;

    protected $table = 'inquilinos';

    protected $fillable = [
        'nombre_inquilino',
        'nro_apartamento',
        'monto_deuda',
        'fecha_deuda',
        'monto_ultimo_pago',
        'fecha_ultimo_pago'
    ];

    protected $casts = [
        'fecha_deuda' => 'date',
        'fecha_ultimo_pago' => 'date',
        'monto_deuda' => 'decimal:2',
        'monto_ultimo_pago' => 'decimal:2'
    ];

    /**
     * Actualizar el pago y recalcular la deuda
     */
    public function actualizarPago($montoPago)
    {
        $this->monto_ultimo_pago = $montoPago;
        $this->fecha_ultimo_pago = Carbon::now();
        $this->monto_deuda = max(0, $this->monto_deuda - $montoPago);
        $this->save();
    }

    /**
     * Obtener el saldo pendiente
     */
    public function getSaldoPendienteAttribute()
    {
        return $this->monto_deuda;
    }

    /**
     * Verificar si tiene deuda pendiente
     */
    public function tieneDeudaPendiente()
    {
        return $this->monto_deuda > 0;
    }
}