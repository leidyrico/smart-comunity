<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $fillable = [
        'nombre',
        'rif',
        'telefono',
        'email',
        'direccion',
        'contacto',
        'estatus'
    ];

    protected $casts = [
        'estatus' => 'string'
    ];

    /**
     * Relación con egresos
     */
    public function egresos()
    {
        return $this->hasMany(Egreso::class);
    }

    /**
     * Scope para proveedores activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estatus', 'activo');
    }
}
