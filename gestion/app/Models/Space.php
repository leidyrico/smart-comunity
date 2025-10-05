<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Reservation;

class Space extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'precio_por_dia',
        'activo'
    ];

    protected $casts = [
        'precio_por_dia' => 'decimal:2',
        'activo' => 'boolean'
    ];

    // Relación con reservas
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    // Scope para espacios activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
