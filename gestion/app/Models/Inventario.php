<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventario extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'categoria',
        'cantidad',
        'precio_unitario',
        'ubicacion',
        'estado',
        'fecha_adquisicion',
        'proveedor',
        'observaciones'
    ];

    protected $casts = [
        'fecha_adquisicion' => 'date',
        'precio_unitario' => 'decimal:2'
    ];

    // Scopes para filtros
    public function scopeDisponible($query)
    {
        return $query->where('estado', 'disponible');
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    // Accessor para el valor total
    public function getValorTotalAttribute()
    {
        return $this->cantidad * $this->precio_unitario;
    }
}
