<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ReciboGastoComun;
use App\Models\Apartamento;
use Illuminate\Http\Request;

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

        $recibos = $query->orderBy('fecha_emision', 'desc')->get();
        return view('recibos.index', compact('recibos'));
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
}
