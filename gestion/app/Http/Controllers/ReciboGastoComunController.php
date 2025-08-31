<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ReciboGastoComun;
use App\Models\Apartamento;
use App\Models\Pago;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RecibosImport;
use App\Exports\RecibosExport;

class ReciboGastoComunController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ReciboGastoComun::query();

        // Filtros
        if ($request->filled('numero_recibo')) {
            $query->where('numero_recibo', 'like', '%' . $request->numero_recibo . '%');
        }

        if ($request->filled('periodo')) {
            $query->where('periodo', 'like', '%' . $request->periodo . '%');
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $recibos = $query->orderBy('periodo', 'desc')->orderBy('fecha_emision', 'desc')->get();
        
        // Calcular estadísticas de recibos vencidos
        $estadisticas = [
            'total_recibos' => ReciboGastoComun::count(),
            'recibos_activos' => ReciboGastoComun::where('estado', 'activo')->count(),
            'recibos_vencidos' => ReciboGastoComun::where('estado', 'vencido')->count(),
            'recibos_anulados' => ReciboGastoComun::where('estado', 'anulado')->count(),
            'total_valor_vencidos' => ReciboGastoComun::where('estado', 'vencido')->sum('total_recibo'),
            'total_valor_activos' => ReciboGastoComun::where('estado', 'activo')->sum('total_recibo'),
            'recibos_vencidos_detalle' => ReciboGastoComun::where('estado', 'vencido')
                ->orderBy('fecha_vencimiento', 'asc')
                ->take(5)
                ->get(['numero_recibo', 'periodo', 'fecha_vencimiento', 'total_recibo'])
        ];
        
        return view('recibos.index', compact('recibos', 'estadisticas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('recibos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'numero_recibo' => 'required|string|max:50|unique:recibo_gasto_comuns,numero_recibo',
            'periodo' => 'required|string|max:50',
            'fecha_emision' => 'required|date',
            'fecha_vencimiento' => 'required|date|after:fecha_emision',
            'valor_administracion' => 'required|numeric|min:0',
            'valor_aseo' => 'nullable|numeric|min:0',
            'valor_vigilancia' => 'nullable|numeric|min:0',
            'valor_mantenimiento' => 'nullable|numeric|min:0',
            'otros_conceptos' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string|max:1000',
            'estado' => 'required|in:activo,vencido,anulado'
        ]);

        $recibo = new ReciboGastoComun($request->all());
        $recibo->calcularTotal();
        $recibo->save();

        // Si el recibo está activo, asignarlo a todos los apartamentos
        if ($recibo->estado === 'activo') {
            $this->asignarReciboATodosApartamentos($recibo);
        }

        return redirect()->route('recibos.index')
            ->with('success', 'Recibo de gasto común creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ReciboGastoComun $recibo)
    {
        $recibo->load('pagos.apartamento');
        $apartamentos = Apartamento::orderBy('numero')->get();
        return view('recibos.show', compact('recibo', 'apartamentos'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReciboGastoComun $recibo)
    {
        return view('recibos.edit', compact('recibo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReciboGastoComun $recibo)
    {
        $request->validate([
            'numero_recibo' => 'required|string|max:50|unique:recibo_gasto_comuns,numero_recibo,' . $recibo->id,
            'periodo' => 'required|string|max:50',
            'fecha_emision' => 'required|date',
            'fecha_vencimiento' => 'required|date|after:fecha_emision',
            'valor_administracion' => 'required|numeric|min:0',
            'valor_aseo' => 'nullable|numeric|min:0',
            'valor_vigilancia' => 'nullable|numeric|min:0',
            'valor_mantenimiento' => 'nullable|numeric|min:0',
            'otros_conceptos' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string|max:1000',
            'estado' => 'required|in:activo,vencido,anulado'
        ]);

        $recibo->fill($request->all());
        $recibo->calcularTotal();
        $recibo->save();

        return redirect()->route('recibos.index')
            ->with('success', 'Recibo de gasto común actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReciboGastoComun $recibo)
    {
        $recibo->delete();
        return redirect()->route('recibos.index')
            ->with('success', 'Recibo de gasto común eliminado exitosamente.');
    }

    /**
     * Mostrar vista de impresión del recibo
     */
    public function print(ReciboGastoComun $recibo)
    {
        $recibo->load('pagos.apartamento');
        return view('recibos.print', compact('recibo'));
    }



    /**
     * Mostrar formulario de importación de recibos
     */
    public function import()
    {
        return view('recibos.import');
    }

    /**
     * Procesar importación de recibos desde CSV
     */
    public function importProcess(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('csv_file');
        $csvData = array_map('str_getcsv', file($file->getRealPath()));
        $header = array_shift($csvData);

        // Limpiar headers de espacios y caracteres especiales
        $header = array_map(function($col) {
            return preg_replace('/[^a-zA-Z0-9]/', '', trim($col));
        }, $header);

        // Columnas requeridas
        $requiredColumns = ['numerorecibo', 'periodo', 'fechaemision', 'fechavencimiento', 'valoradministracion', 'valoraseo', 'valorvigilancia', 'valormantenimiento', 'otrosconceptos', 'estado'];
        
        // Verificar columnas requeridas
        $missingColumns = array_diff($requiredColumns, $header);
        if (!empty($missingColumns)) {
            return redirect()->back()->withErrors([
                'csv_file' => 'El archivo CSV debe contener las columnas: ' . implode(', ', $requiredColumns)
            ]);
        }

        $recibosCreados = 0;
        $errores = [];

        foreach ($csvData as $index => $row) {
            if (count($row) !== count($header)) {
                continue; // Saltar filas incompletas
            }

            $data = array_combine($header, $row);
            
            try {
                // Validar campos requeridos
                if (empty($data['numerorecibo']) || empty($data['periodo']) || empty($data['fechaemision'])) {
                    $errores[] = "Fila " . ($index + 2) . ": Campos requeridos faltantes";
                    continue;
                }

                // Verificar si ya existe el recibo
                $existeRecibo = ReciboGastoComun::where('numero_recibo', $data['numerorecibo'])->exists();
                if ($existeRecibo) {
                    $errores[] = "Fila " . ($index + 2) . ": El recibo {$data['numerorecibo']} ya existe";
                    continue;
                }

                $recibo = new ReciboGastoComun([
                    'numero_recibo' => $data['numerorecibo'],
                    'periodo' => $data['periodo'],
                    'fecha_emision' => $data['fechaemision'],
                    'fecha_vencimiento' => $data['fechavencimiento'],
                    'valor_administracion' => floatval($data['valoradministracion'] ?? 0),
                    'valor_aseo' => floatval($data['valoraseo'] ?? 0),
                    'valor_vigilancia' => floatval($data['valorvigilancia'] ?? 0),
                    'valor_mantenimiento' => floatval($data['valormantenimiento'] ?? 0),
                    'otros_conceptos' => floatval($data['otrosconceptos'] ?? 0),
                    'estado' => $data['estado'] ?? 'activo'
                ]);

                $recibo->calcularTotal();
                $recibo->save();
                
                // Si el recibo está activo, asignarlo a todos los apartamentos
                if ($recibo->estado === 'activo') {
                    $this->asignarReciboATodosApartamentos($recibo);
                }
                
                $recibosCreados++;

            } catch (\Exception $e) {
                $errores[] = "Fila " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        $mensaje = "Se importaron {$recibosCreados} recibos exitosamente.";
        if (!empty($errores)) {
            $mensaje .= " Errores encontrados: " . implode(', ', array_slice($errores, 0, 5));
            if (count($errores) > 5) {
                $mensaje .= " y " . (count($errores) - 5) . " más.";
            }
        }

        return redirect()->route('recibos.index')->with('success', $mensaje);
    }

    /**
     * Descargar plantilla CSV para importación de recibos
     */
    public function template()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="plantilla_recibos.csv"',
        ];

        $columns = [
            'numero_recibo',
            'periodo', 
            'fecha_emision',
            'fecha_vencimiento',
            'valor_administracion',
            'valor_aseo',
            'valor_vigilancia', 
            'valor_mantenimiento',
            'otros_conceptos',
            'estado'
        ];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            // Agregar filas de ejemplo
            fputcsv($file, ['REC-2024-01-101', '2024-01', '2024-01-01', '2024-01-31', '150000', '25000', '30000', '20000', '5000', 'activo']);
            fputcsv($file, ['REC-2024-01-102', '2024-01', '2024-01-01', '2024-01-31', '150000', '25000', '30000', '20000', '0', 'activo']);
            fputcsv($file, ['REC-2024-01-103', '2024-01', '2024-01-01', '2024-01-31', '150000', '25000', '30000', '20000', '10000', 'activo']);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Eliminar múltiples recibos seleccionados
     */
    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'recibo_ids' => 'required|array|min:1',
            'recibo_ids.*' => 'exists:recibo_gasto_comuns,id'
        ]);

        try {
            $count = ReciboGastoComun::whereIn('id', $request->recibo_ids)->delete();
            
            return redirect()->route('recibos.index')
                ->with('success', "Se eliminaron {$count} recibos exitosamente.");
        } catch (\Exception $e) {
            return redirect()->route('recibos.index')
                ->with('error', 'Error al eliminar los recibos: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar todos los recibos
     */
    public function destroyAll()
    {
        try {
            $count = ReciboGastoComun::count();
            
            if ($count === 0) {
                return redirect()->route('recibos.index')
                    ->with('info', 'No hay recibos para eliminar.');
            }
            
            // Eliminar todos los recibos (esto también eliminará los pagos asociados por cascada)
            ReciboGastoComun::truncate();
            
            return redirect()->route('recibos.index')
                ->with('success', "Se eliminaron todos los {$count} recibos exitosamente.");
        } catch (\Exception $e) {
            return redirect()->route('recibos.index')
                ->with('error', 'Error al eliminar todos los recibos: ' . $e->getMessage());
        }
    }

    /**
     * Asignar recibo a todos los apartamentos
     */
    private function asignarReciboATodosApartamentos(ReciboGastoComun $recibo)
    {
        // Si es un recibo vencido, NO asignarlo automáticamente
        // Los recibos vencidos solo se asignan manualmente a través de recibos/asignar-manual
        if ($recibo->estado === 'vencido') {
            \Log::info('Recibo vencido no asignado automáticamente', [
                'recibo_id' => $recibo->id,
                'numero_recibo' => $recibo->numero_recibo,
                'motivo' => 'Los recibos vencidos solo se asignan manualmente'
            ]);
            return;
        }
        
        $apartamentos = Apartamento::all();
        
        foreach ($apartamentos as $apartamento) {
            // Solo para recibos activos - determinar el estado del pago
            $estadoPago = 'pendiente_confirmacion';
            $observaciones = 'Recibo asignado automáticamente';
            
            // Crear registro de pago para cada apartamento
            Pago::create([
                'recibo_gasto_comun_id' => $recibo->id,
                'apartamento_id' => $apartamento->id,
                'monto_pagado' => 0,
                'fecha_pago' => null,
                'metodo_pago' => null,
                'numero_comprobante' => null,
                'observaciones' => $observaciones,
                'estado' => $estadoPago
            ]);
            
            // Solo cambiar a deudor si actualmente es solvente
            // Los que ya son deudores mantienen su estatus
            if ($apartamento->estatus_financiero === 'solvente') {
                $apartamento->update([
                    'estatus_financiero' => 'deudor',
                    'fecha_cambio_estatus' => now()->toDateString()
                ]);
            }
        }
    }

    /**
     * Mostrar vista para asignación manual de recibos vencidos
     */
    public function showAsignarRecibosManual()
    {
        // Obtener todos los recibos vencidos/activos (pueden tener asignaciones existentes)
        $recibosDisponibles = ReciboGastoComun::whereIn('estado', ['vencido', 'activo'])
            ->with(['pagos.apartamento']) // Cargar asignaciones existentes
            ->orderBy('fecha_emision', 'desc')
            ->get();

        // Obtener todos los apartamentos activos
        $apartamentos = Apartamento::where('estado', 'ocupado')
            ->orderBy('numero')
            ->get();

        // Calcular monto total disponible
        $totalMontoDisponible = $recibosDisponibles->sum('total_recibo');

        return view('recibos.asignar-manual', compact(
            'recibosDisponibles',
            'apartamentos',
            'totalMontoDisponible'
        ));
    }

    /**
     * Procesar asignaciones manuales de recibos a apartamentos
     */
    public function asignarRecibosManual(Request $request)
    {
        $request->validate([
            'recibos_seleccionados' => 'required|array|min:1',
            'recibos_seleccionados.*' => 'exists:recibo_gasto_comuns,id',
            'apartamentos' => 'required|array',
        ], [
            'recibos_seleccionados.required' => 'Debe seleccionar al menos un recibo.',
            'recibos_seleccionados.min' => 'Debe seleccionar al menos un recibo.',
            'apartamentos.required' => 'Debe asignar apartamentos a los recibos seleccionados.',
        ]);

        try {
            \DB::beginTransaction();

            $recibosSeleccionados = $request->recibos_seleccionados;
            $apartamentosAsignados = $request->apartamentos;
            $asignacionesCreadas = 0;
            $errores = [];

            foreach ($recibosSeleccionados as $reciboId) {
                // Verificar que el recibo tenga un apartamento asignado
                if (!isset($apartamentosAsignados[$reciboId]) || empty($apartamentosAsignados[$reciboId])) {
                    continue; // Saltar recibos sin apartamento asignado
                }

                $apartamentoId = $apartamentosAsignados[$reciboId];
                
                // Verificar que el recibo existe
                $recibo = ReciboGastoComun::where('id', $reciboId)
                    ->whereIn('estado', ['vencido', 'activo'])
                    ->first();

                if (!$recibo) {
                    $errores[] = "El recibo ID {$reciboId} no existe o no está disponible.";
                    continue;
                }

                // Verificar que el apartamento existe
                $apartamento = Apartamento::find($apartamentoId);
                if (!$apartamento) {
                    $errores[] = "El apartamento ID {$apartamentoId} no existe.";
                    continue;
                }

                // Verificar si ya existe una asignación para esta combinación recibo-apartamento
                $asignacionExistente = Pago::where('recibo_gasto_comun_id', $recibo->id)
                    ->where('apartamento_id', $apartamento->id)
                    ->first();

                if ($asignacionExistente) {
                    $errores[] = "El recibo {$recibo->numero_recibo} ya está asignado al apartamento {$apartamento->numero}.";
                    continue;
                }

                // Crear el pago (asignación) siempre como pendiente_confirmacion
                $estadoPago = 'pendiente_confirmacion';
                $observaciones = 'Asignación manual de recibo';

                // Crear el pago (asignación)
                Pago::create([
                    'apartamento_id' => $apartamento->id,
                    'recibo_gasto_comun_id' => $recibo->id,
                    'monto_pagado' => 0,
                    'fecha_pago' => null,
                    'metodo_pago' => 'pendiente',
                    'estado' => $estadoPago,
                    'observaciones' => $observaciones
                ]);

                $asignacionesCreadas++;
            }

            // Actualizar estatus financiero de todos los apartamentos afectados
            $apartamentosAfectados = collect($apartamentosAsignados)->unique()->values();
            foreach ($apartamentosAfectados as $apartamentoId) {
                $apartamento = Apartamento::find($apartamentoId);
                if ($apartamento) {
                    // Contar recibos activos o vencidos asignados
                    $recibosActivosVencidos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])
                        ->whereHas('pagos', function($query) use ($apartamento) {
                            $query->where('apartamento_id', $apartamento->id);
                        })->count();
                    
                    // Determinar nuevo estatus según criterios
                    if ($recibosActivosVencidos == 0) {
                        $nuevoEstatus = 'solvente';
                    } elseif ($recibosActivosVencidos > 3) {
                        $nuevoEstatus = 'moroso';
                    } else {
                        $nuevoEstatus = 'deudor';
                    }
                    
                    // Actualizar si es necesario
                    if ($apartamento->estatus_financiero !== $nuevoEstatus) {
                        $apartamento->update([
                            'estatus_financiero' => $nuevoEstatus,
                            'fecha_cambio_estatus' => now()->toDateString()
                        ]);
                    }
                }
            }

            \DB::commit();

            $mensaje = "Asignación completada exitosamente. {$asignacionesCreadas} recibos asignados.";
            if (!empty($errores)) {
                $mensaje .= " Errores encontrados: " . implode(', ', $errores);
            }

            return redirect()->route('recibos.asignar-manual')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            \DB::rollback();
            return redirect()->route('recibos.asignar-manual')
                ->with('error', 'Error durante la asignación: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar una asignación de recibo a un apartamento
     */
    public function eliminarAsignacion($reciboId, $apartamentoId)
    {
        try {
            \DB::beginTransaction();

            // Buscar el pago (asignación) específico
            $pago = Pago::where('recibo_gasto_comun_id', $reciboId)
                        ->where('apartamento_id', $apartamentoId)
                        ->where('estado', 'pendiente_confirmacion')
                        ->first();

            if (!$pago) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la asignación especificada o ya fue confirmada.'
                ], 404);
            }

            // Eliminar la asignación
            $pago->delete();

            // Actualizar el estatus financiero del apartamento
            $apartamento = Apartamento::find($apartamentoId);
            if ($apartamento) {
                // Contar recibos activos y vencidos asignados al apartamento
                $recibosActivosVencidos = Pago::whereHas('reciboGastoComun', function ($query) {
                    $query->where('estado', 'activo')
                          ->where('fecha_vencimiento', '<', now());
                })->where('apartamento_id', $apartamento->id)
                  ->where('estado', 'pendiente_confirmacion')
                  ->count();

                // Determinar nuevo estatus según criterios
                if ($recibosActivosVencidos == 0) {
                    $nuevoEstatus = 'solvente';
                } elseif ($recibosActivosVencidos > 3) {
                    $nuevoEstatus = 'moroso';
                } else {
                    $nuevoEstatus = 'deudor';
                }

                // Actualizar si es necesario
                if ($apartamento->estatus_financiero !== $nuevoEstatus) {
                    $apartamento->update([
                        'estatus_financiero' => $nuevoEstatus,
                        'fecha_cambio_estatus' => now()->toDateString()
                    ]);
                }
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Asignación eliminada exitosamente.'
            ]);

        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la asignación: ' . $e->getMessage()
            ], 500);
        }
    }
}
