<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Mail\ComprobantePago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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

        $pagos = $query->orderBy('fecha_pago', 'desc')->paginate(15);
        $apartamentos = Apartamento::orderBy('numero')->get();
        
        return view('pagos.index', compact('pagos', 'apartamentos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $apartamentos = Apartamento::orderBy('numero')->get();
        $recibos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])->orderBy('fecha_emision', 'desc')->get();
        
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
            'metodo_pago' => 'required|in:efectivo,pago_movil,transferencia',
            'numero_comprobante' => 'nullable|string|max:100|unique:pagos,numero_comprobante',
            'observaciones' => 'nullable|string|max:1000',
            'estado' => 'required|in:confirmado,pendiente_confirmacion,rechazado'
        ]);

        $pago = Pago::create($request->all());
        
        // Cargar las relaciones necesarias para el correo
        $pago->load(['apartamento', 'reciboGastoComun']);
        
        // Enviar correo de confirmación si el apartamento tiene email
        if ($pago->apartamento->email) {
            try {
                Mail::to($pago->apartamento->email)->send(new ComprobantePago($pago));
                $mensaje = 'Pago registrado exitosamente y correo de confirmación enviado.';
            } catch (\Exception $e) {
                \Log::error('Error enviando correo de confirmación: ' . $e->getMessage());
                $mensaje = 'Pago registrado exitosamente, pero hubo un error al enviar el correo de confirmación.';
            }
        } else {
            $mensaje = 'Pago registrado exitosamente. No se pudo enviar correo (apartamento sin email registrado).';
        }

        return redirect()->route('pagos.index')
            ->with('success', $mensaje);
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
        $recibos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])->orderBy('fecha_emision', 'desc')->get();
        
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
            'metodo_pago' => 'required|in:efectivo,pago_movil,transferencia',
            'numero_comprobante' => 'nullable|string|max:100|unique:pagos,numero_comprobante,' . $pago->id,
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
        // Obtener información del pago antes de eliminarlo para el mensaje
        $numeroRecibo = $pago->reciboGastoComun->numero_recibo;
        $numeroApartamento = $pago->apartamento->numero;
        $montoPagado = $pago->monto_pagado;
        
        $pago->delete();
        
        return redirect()->route('deudas.index')
            ->with('success', "Pago de $" . number_format($montoPagado, 2, ',', '.') . " eliminado exitosamente. El recibo {$numeroRecibo} del apartamento {$numeroApartamento} ahora tiene saldo pendiente.");
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
        
        // Obtener recibos activos y vencidos que no estén completamente pagados
        $recibos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])
            ->whereRaw('total_recibo > (SELECT COALESCE(SUM(monto_pagado), 0) FROM pagos WHERE recibo_gasto_comun_id = recibo_gasto_comuns.id AND apartamento_id = ? AND estado = "confirmado")', [$apartamentoId])
            ->orderBy('fecha_emision', 'desc')
            ->get(['id', 'numero_recibo', 'periodo', 'total_recibo', 'fecha_vencimiento']);
            
        return response()->json($recibos);
    }

    /**
     * Obtener saldo pendiente de un recibo específico para un apartamento (AJAX)
     */
    public function getSaldoRecibo(Request $request)
    {
        $apartamentoId = $request->apartamento_id;
        $reciboId = $request->recibo_id;
        
        if (!$apartamentoId || !$reciboId) {
            return response()->json(['error' => 'Parámetros requeridos'], 400);
        }
        
        $recibo = ReciboGastoComun::find($reciboId);
        if (!$recibo) {
            return response()->json(['error' => 'Recibo no encontrado'], 404);
        }
        
        // Calcular total pagado para este recibo y apartamento
        $totalPagado = Pago::where('apartamento_id', $apartamentoId)
            ->where('recibo_gasto_comun_id', $reciboId)
            ->where('estado', 'confirmado')
            ->sum('monto_pagado');
            
        $saldoPendiente = $recibo->total_recibo - $totalPagado;
        
        return response()->json([
            'total_recibo' => $recibo->total_recibo,
            'total_pagado' => $totalPagado,
            'saldo_pendiente' => max(0, $saldoPendiente)
        ]);
    }

    /**
     * Mostrar formulario para pago global (sin recibo específico)
     */
    public function createGlobal(Apartamento $apartamento)
    {
        // Obtener TODOS los recibos activos y vencidos del sistema
        $todosLosRecibos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        $recibos_pendientes = collect();
        $total_pendiente = 0;

        foreach ($todosLosRecibos as $recibo) {
            // Calcular total pagado para este recibo y apartamento específico
            $totalPagado = Pago::where('apartamento_id', $apartamento->id)
                ->where('recibo_gasto_comun_id', $recibo->id)
                ->where('estado', 'confirmado')
                ->sum('monto_pagado');
            
            $saldoPendiente = $recibo->total_recibo - $totalPagado;
            
            // Solo incluir recibos con saldo pendiente > 0
            if ($saldoPendiente > 0) {
                // Agregar el saldo pendiente calculado al recibo
                $recibo->saldo_pendiente_apartamento = $saldoPendiente;
                $recibos_pendientes->push($recibo);
                $total_pendiente += $saldoPendiente;
            }
        }

        return view('pagos.create-global', compact('apartamento', 'recibos_pendientes', 'total_pendiente'));
    }

    /**
     * Procesar pago global distribuyéndolo entre el recibo activo y los recibos vencidos más antiguos
     */
    public function storeGlobal(Request $request)
    {
        $request->validate([
            'apartamento_id' => 'required|exists:apartamentos,id',
            'monto_total' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
            'metodo_pago' => 'required|in:efectivo,pago_movil,transferencia',
            'numero_comprobante' => 'nullable|string|max:100|unique:pagos,numero_comprobante',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $apartamento = Apartamento::find($request->apartamento_id);
        $montoRestante = $request->monto_total;
        $pagosCreados = [];
        
        // Obtener el recibo activo más reciente
        $reciboActivo = ReciboGastoComun::where('estado', 'activo')
            ->whereHas('pagos', function($query) use ($apartamento) {
                $query->where('apartamento_id', $apartamento->id);
            })
            ->with(['pagos' => function($query) use ($apartamento) {
                $query->where('apartamento_id', $apartamento->id)
                      ->where('estado', 'confirmado');
            }])
            ->orderBy('fecha_emision', 'desc')
            ->first();
            
        // Obtener recibos vencidos ordenados por fecha de emisión (más antiguos primero)
        $recibosVencidos = ReciboGastoComun::where('estado', 'vencido')
            ->whereHas('pagos', function($query) use ($apartamento) {
                $query->where('apartamento_id', $apartamento->id);
            })
            ->with(['pagos' => function($query) use ($apartamento) {
                $query->where('apartamento_id', $apartamento->id)
                      ->where('estado', 'confirmado');
            }])
            ->orderBy('fecha_emision', 'asc')
            ->get();
            
        // Crear una colección ordenada: primero el recibo activo, luego los vencidos
        $recibosParaProcesar = collect();
        if ($reciboActivo) {
            $recibosParaProcesar->push($reciboActivo);
        }
        $recibosParaProcesar = $recibosParaProcesar->merge($recibosVencidos);

        foreach ($recibosParaProcesar as $recibo) {
            if ($montoRestante <= 0) break;
            
            // Calcular saldo pendiente del recibo
            $totalPagado = $recibo->pagos->sum('monto_pagado');
            $saldoPendiente = $recibo->total_recibo - $totalPagado;
            
            if ($saldoPendiente > 0) {
                // Determinar cuánto pagar de este recibo
                $montoPagar = min($montoRestante, $saldoPendiente);
                
                // Crear el pago
                $pago = Pago::create([
                    'apartamento_id' => $apartamento->id,
                    'recibo_gasto_comun_id' => $recibo->id,
                    'monto_pagado' => $montoPagar,
                    'fecha_pago' => $request->fecha_pago,
                    'metodo_pago' => $request->metodo_pago,
                    'numero_comprobante' => $request->numero_comprobante,
                    'observaciones' => ($request->observaciones ?? '') . ' | Pago global distribuido automáticamente',
                    'estado' => 'confirmado'
                ]);
                
                $pagosCreados[] = $pago;
                $montoRestante -= $montoPagar;
            }
        }
        
        // Si queda monto restante, crear un pago pendiente sin recibo específico
        if ($montoRestante > 0) {
            // Buscar el primer recibo disponible para asociar el monto restante
            $primerRecibo = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])
                ->whereHas('pagos', function($query) use ($apartamento) {
                    $query->where('apartamento_id', $apartamento->id);
                })
                ->orderBy('fecha_emision', 'desc')
                ->first();
                
            if ($primerRecibo) {
                $pago = Pago::create([
                    'apartamento_id' => $apartamento->id,
                    'recibo_gasto_comun_id' => $primerRecibo->id,
                    'monto_pagado' => $montoRestante,
                    'fecha_pago' => $request->fecha_pago,
                    'metodo_pago' => $request->metodo_pago,
                    'numero_comprobante' => $request->numero_comprobante,
                    'observaciones' => ($request->observaciones ?? '') . ' | Pago global - monto excedente',
                    'estado' => 'confirmado'
                ]);
                
                $pagosCreados[] = $pago;
            }
        }
        
        // Enviar correo de confirmación si el apartamento tiene email
        if ($apartamento->email && !empty($pagosCreados)) {
            try {
                // Enviar correo con el primer pago creado como referencia
                Mail::to($apartamento->email)->send(new ComprobantePago($pagosCreados[0]));
                $mensaje = 'Pago global procesado exitosamente. Se distribuyó entre ' . count($pagosCreados) . ' recibo(s). Correo de confirmación enviado.';
            } catch (\Exception $e) {
                \Log::error('Error enviando correo de confirmación: ' . $e->getMessage());
                $mensaje = 'Pago global procesado exitosamente. Se distribuyó entre ' . count($pagosCreados) . ' recibo(s). Error al enviar correo de confirmación.';
            }
        } else {
            $mensaje = 'Pago global procesado exitosamente. Se distribuyó entre ' . count($pagosCreados) . ' recibo(s).';
        }
        
        return redirect()->route('deudas.show', $apartamento)
            ->with('success', $mensaje);
    }
}
