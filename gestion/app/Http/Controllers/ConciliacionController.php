<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pago;
use App\Models\Egreso;
use Carbon\Carbon;

class ConciliacionController extends Controller
{
    public function index()
    {
        // Obtener todos los ingresos (pagos confirmados con monto > 0)
        $ingresos = Pago::where('estado', 'confirmado')
            ->where('monto_pagado', '>', 0)
            ->with(['apartamento', 'reciboGastoComun'])
            ->orderBy('fecha_pago', 'desc')
            ->get();

        // Obtener todos los egresos
        $egresos = Egreso::orderBy('fecha', 'desc')->get();

        // Calcular totales
        $totalIngresos = $ingresos->sum('monto_pagado');
        $totalEgresos = $egresos->sum('monto');
        $balance = $totalIngresos - $totalEgresos;

        return view('conciliacion.index', compact('ingresos', 'egresos', 'totalIngresos', 'totalEgresos', 'balance'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nro_factura' => 'required|string|max:255',
            'fecha' => 'required|date',
            'comprobante' => 'nullable|string|max:255',
            'monto' => 'required|numeric|min:0.01',
            'descripcion' => 'nullable|string'
        ]);

        Egreso::create($request->all());

        return redirect()->route('conciliacion.index')
            ->with('success', 'Egreso registrado exitosamente.');
    }

    public function destroy($id)
    {
        $egreso = Egreso::findOrFail($id);
        $egreso->delete();

        return redirect()->route('conciliacion.index')
            ->with('success', 'Egreso eliminado exitosamente.');
    }
}
