<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Egreso extends Model
{
    protected $table = 'egresos_new';

    protected $fillable = [
        'nro_factura',
        'fecha',
        'comprobante',
        'monto',
        'monto_en_bs',
        'descripcion',
        'proveedor_id'
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
        'monto_en_bs' => 'decimal:2'
    ];

    /**
     * Relación con proveedor
     */
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }
}
