<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pago;
use App\Models\Egreso;
use App\Models\ReciboGastoComun;
use App\Models\Apartamento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ConciliacionController extends Controller
{
    public function index(Request $request)
    {
        // Obtener el filtro de mes si existe
        $mesSeleccionado = $request->get('mes');
        
        // Query base para ingresos (pagos confirmados con monto > 0)
        $queryIngresos = Pago::confirmadosReales()
            ->with(['apartamento', 'reciboGastoComun']);

        // Query base para egresos
        $queryEgresos = Egreso::query();

        // Aplicar filtro por mes si está presente
        if ($mesSeleccionado) {
            // Convertir el mes seleccionado (YYYY-MM) a rango de fechas
            $fechaInicio = Carbon::createFromFormat('Y-m', $mesSeleccionado)->startOfMonth();
            $fechaFin = Carbon::createFromFormat('Y-m', $mesSeleccionado)->endOfMonth();
            
            // Filtrar ingresos por mes
            $queryIngresos->whereBetween('fecha_pago', [$fechaInicio, $fechaFin]);
            
            // Filtrar egresos por mes
            $queryEgresos->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }

        // Calcular totales antes de la paginación
        $totalIngresos = $queryIngresos->sum('monto_pagado');
        $totalIngresosEnBs = $queryIngresos->sum('monto_en_bs');
        $totalEgresos = $queryEgresos->sum('monto');
        $totalEgresosEnBs = $queryEgresos->sum('monto_en_bs');
        $balance = $totalIngresos - $totalEgresos;
        $balanceEnBs = $totalIngresosEnBs - $totalEgresosEnBs;

        // Ejecutar las consultas con paginación
        $ingresos = $queryIngresos->orderBy('fecha_pago', 'desc')->paginate(15, ['*'], 'ingresos_page');
        $egresos = $queryEgresos->orderBy('fecha', 'desc')->paginate(15, ['*'], 'egresos_page');

        // Obtener lista de meses disponibles para el selector
        $mesesDisponibles = $this->obtenerMesesDisponibles();

        return view('conciliacion.index', compact(
            'ingresos', 
            'egresos', 
            'totalIngresos', 
            'totalIngresosEnBs',
            'totalEgresos',
            'totalEgresosEnBs', 
            'balance',
            'balanceEnBs', 
            'mesSeleccionado',
            'mesesDisponibles'
        ));
    }

    /**
     * Obtener lista de meses disponibles basados en los datos existentes
     */
    private function obtenerMesesDisponibles()
    {
        // Obtener meses de ingresos
        $mesesIngresos = Pago::confirmadosReales()
            ->whereNotNull('fecha_pago')
            ->selectRaw('DATE_FORMAT(fecha_pago, "%Y-%m") as mes')
            ->distinct()
            ->pluck('mes');

        // Obtener meses de egresos
        $mesesEgresos = Egreso::selectRaw('DATE_FORMAT(fecha, "%Y-%m") as mes')
            ->distinct()
            ->pluck('mes');

        // Combinar y ordenar
        $meses = $mesesIngresos->merge($mesesEgresos)
            ->unique()
            ->filter() // Remover valores nulos
            ->sort()
            ->values();

        // Convertir a formato legible
        return $meses->map(function($mes) {
            $fecha = Carbon::createFromFormat('Y-m', $mes);
            return [
                'valor' => $mes,
                'texto' => $fecha->locale('es')->isoFormat('MMMM YYYY')
            ];
        });
    }

    public function store(Request $request)
    {
        if (Auth::check() && Auth::user()->isUsuarioPropietario()) {
            abort(403);
        }
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
        if (Auth::check() && Auth::user()->isUsuarioPropietario()) {
            abort(403);
        }
        $egreso = Egreso::findOrFail($id);
        $egreso->delete();

        return redirect()->route('conciliacion.index')
            ->with('success', 'Egreso eliminado exitosamente.');
    }

    /**
     * Mostrar la vista de recaudación
     */
    public function recaudacion()
    {
        return view('conciliacion.recaudacion');
    }

    /**
     * API para obtener lista de recibos con filtros
     */
    public function getRecibosApi(Request $request)
    {
        $query = ReciboGastoComun::with(['pagos' => function($query) {
            $query->where('estado', 'confirmado')
                  ->where('monto_pagado', '>', 0)
                  ->sinPruebas();
        }]);

        // Aplicar filtros
        if ($request->has('numero') && $request->numero) {
            $query->where('numero_recibo', 'like', '%' . $request->numero . '%');
        }

        if ($request->has('periodo') && $request->periodo) {
            // Convertir formato YYYY-MM a texto del período
            $fecha = Carbon::createFromFormat('Y-m', $request->periodo);
            $periodoTexto = $fecha->locale('es')->isoFormat('MMMM YYYY');
            $query->where('periodo', 'like', '%' . $periodoTexto . '%');
        }

        // Obtener total de apartamentos una sola vez
        $totalApartamentos = Apartamento::count();
        
        $recibos = $query->orderBy('periodo', 'desc')
            ->paginate(25) // Paginación de 25 recibos por página
            ->through(function($recibo) use ($totalApartamentos) {
                // Contar apartamentos que han pagado este recibo
                $apartamentosPagados = $recibo->pagos
                    ->unique('apartamento_id')
                    ->count();
                
                return [
                    'id' => $recibo->id,
                    'numero' => $recibo->numero_recibo,
                    'periodo' => $recibo->periodo,
                    'total_apartamentos' => $totalApartamentos,
                    'apartamentos_pagados' => $apartamentosPagados,
                ];
            });

        return response()->json($recibos);
    }

    /**
     * Obtener detalle de apartamentos que pagaron un recibo específico
     */
    public function detalleRecaudacion($reciboId)
    {
        $recibo = ReciboGastoComun::findOrFail($reciboId);
        
        // Obtener pagos confirmados para este recibo con información del apartamento
        $pagosDetalle = Pago::where('recibo_gasto_comun_id', $reciboId)
            ->confirmadosReales()
            ->with('apartamento')
            ->orderBy('fecha_pago', 'desc')
            ->get()
            ->map(function($pago) {
                return [
                    'apartamento' => $pago->apartamento->numero,
                    'propietario' => $pago->apartamento->propietario,
                    'monto' => $pago->monto_pagado,
                    'fecha_pago' => $pago->fecha_pago,
                    'metodo_pago' => $pago->metodo_pago ?? 'N/A'
                ];
            });

        // Contar apartamentos únicos que han pagado (excluyendo pagos de prueba)
        $apartamentosPagados = Pago::where('recibo_gasto_comun_id', $reciboId)
            ->confirmadosReales()
            ->distinct('apartamento_id')
            ->count('apartamento_id');

        // Obtener total de apartamentos
        $totalApartamentos = Apartamento::count();

        return response()->json([
            'recibo' => [
                'periodo' => $recibo->periodo,
                'fecha_emision' => $recibo->fecha_emision,
                'fecha_vencimiento' => $recibo->fecha_vencimiento,
                'monto_total' => $recibo->total_recibo,
                'total_apartamentos' => $totalApartamentos
            ],
            'pagos' => $pagosDetalle,
            'apartamentos_pagados' => $apartamentosPagados
        ]);
    }
}
