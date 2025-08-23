<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Pago::with(['apartamento', 'reciboGastoComun']);

        // Filtros
        if ($request->filled('apartamento_id')) {
            $query->where('apartamento_id', $request->apartamento_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->where('fecha_pago', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_pago', '<=', $request->fecha_hasta);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('metodo_pago')) {
            $query->where('metodo_pago', $request->metodo_pago);
        }

        $pagos = $query->orderBy('fecha_pago', 'desc')->get();
        $apartamentos = Apartamento::orderBy('numero')->get();
        
        return view('pagos.index', compact('pagos', 'apartamentos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $apartamentos = Apartamento::orderBy('numero')->get();
        $recibos = ReciboGastoComun::where('estado', 'activo')->orderBy('fecha_emision', 'desc')->get();
        
        // Si viene un apartamento específico desde la URL
        $apartamentoSeleccionado = $request->apartamento_id ? 
            Apartamento::find($request->apartamento_id) : null;
            
        return view('pagos.create', compact('apartamentos', 'recibos', 'apartamentoSeleccionado'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'apartamento_id' => 'required|exists:apartamentos,id',
            'recibo_gasto_comun_id' => 'required|exists:recibo_gasto_comuns,id',
            'monto_pagado' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
            'metodo_pago' => 'required|in:efectivo,transferencia,cheque,tarjeta_credito,tarjeta_debito',
            'numero_comprobante' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string|max:1000',
            'estado' => 'required|in:confirmado,pendiente_confirmacion,rechazado'
        ]);

        $pago = Pago::create($request->all());

        return redirect()->route('pagos.index')
            ->with('success', 'Pago registrado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Pago $pago)
    {
        $pago->load(['apartamento', 'reciboGastoComun']);
        return view('pagos.show', compact('pago'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pago $pago)
    {
        $apartamentos = Apartamento::orderBy('numero')->get();
        $recibos = ReciboGastoComun::where('estado', 'activo')->orderBy('fecha_emision', 'desc')->get();
        
        return view('pagos.edit', compact('pago', 'apartamentos', 'recibos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pago $pago)
    {
        $request->validate([
            'apartamento_id' => 'required|exists:apartamentos,id',
            'recibo_gasto_comun_id' => 'required|exists:recibo_gasto_comuns,id',
            'monto_pagado' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
            'metodo_pago' => 'required|in:efectivo,transferencia,cheque,tarjeta_credito,tarjeta_debito',
            'numero_comprobante' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string|max:1000',
            'estado' => 'required|in:confirmado,pendiente_confirmacion,rechazado'
        ]);

        $pago->update($request->all());

        return redirect()->route('pagos.index')
            ->with('success', 'Pago actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pago $pago)
    {
        $pago->delete();
        return redirect()->route('pagos.index')
            ->with('success', 'Pago eliminado exitosamente.');
    }

    /**
     * Mostrar estado de cuenta de un apartamento específico
     */
    public function estadoCuenta(Apartamento $apartamento)
    {
        $apartamento->load(['pagos.reciboGastoComun', 'recibos']);
        
        // Calcular totales
        $totalPagado = $apartamento->pagos->where('estado', 'confirmado')->sum('monto_pagado');
        $totalRecibos = $apartamento->recibos->sum('total_recibo');
        $saldoPendiente = $totalRecibos - $totalPagado;
        
        return view('pagos.estado-cuenta', compact('apartamento', 'totalPagado', 'totalRecibos', 'saldoPendiente'));
    }

    /**
     * Confirmar un pago pendiente
     */
    public function confirmar(Pago $pago)
    {
        $pago->update(['estado' => 'confirmado']);
        
        return redirect()->back()
            ->with('success', 'Pago confirmado exitosamente.');
    }

    /**
     * Rechazar un pago
     */
    public function rechazar(Request $request, Pago $pago)
    {
        $request->validate([
            'motivo_rechazo' => 'required|string|max:500'
        ]);
        
        $pago->update([
            'estado' => 'rechazado',
            'observaciones' => $pago->observaciones . ' | Motivo rechazo: ' . $request->motivo_rechazo
        ]);
        
        return redirect()->back()
            ->with('success', 'Pago rechazado.');
    }

    /**
     * Obtener recibos de un apartamento específico (AJAX)
     */
    public function getRecibosPorApartamento(Request $request)
    {
        $apartamentoId = $request->apartamento_id;
        
        if (!$apartamentoId) {
            return response()->json([]);
        }
        
        // Obtener recibos activos que no estén completamente pagados
        $recibos = ReciboGastoComun::where('estado', 'activo')
            ->whereRaw('total_recibo > (SELECT COALESCE(SUM(monto_pagado), 0) FROM pagos WHERE recibo_gasto_comun_id = recibo_gasto_comuns.id AND apartamento_id = ? AND estado = "confirmado")', [$apartamentoId])
            ->orderBy('fecha_emision', 'desc')
            ->get(['id', 'numero_recibo', 'periodo', 'total_recibo', 'fecha_vencimiento']);
            
        return response()->json($recibos);
    }
}
