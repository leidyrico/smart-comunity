<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Inquilino;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InquilinoController extends Controller
{
    /**
     * Mostrar lista de inquilinos
     */
    public function index()
    {
        $inquilinos = Inquilino::orderBy('created_at', 'desc')->get();
        return view('inquilinos.index', compact('inquilinos'));
    }

    /**
     * Mostrar formulario de registro
     */
    public function create()
    {
        return view('inquilinos.create');
    }

    /**
     * Guardar nuevo inquilino
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre_inquilino' => 'required|string|max:255',
            'nro_apartamento' => 'required|string|max:50|unique:inquilinos,nro_apartamento',
            'monto_deuda' => 'required|numeric|min:0',
            'monto_ultimo_pago' => 'nullable|numeric|min:0',
            'fecha_ultimo_pago' => 'nullable|date'
        ]);

        $inquilino = new Inquilino();
        $inquilino->nombre_inquilino = $request->nombre_inquilino;
        $inquilino->nro_apartamento = $request->nro_apartamento;
        $inquilino->monto_deuda = $request->monto_deuda;
        $inquilino->fecha_deuda = Carbon::now();
        $inquilino->monto_ultimo_pago = $request->monto_ultimo_pago;
        $inquilino->fecha_ultimo_pago = $request->fecha_ultimo_pago ? Carbon::parse($request->fecha_ultimo_pago) : null;
        $inquilino->save();

        return redirect()->route('inquilinos.index')
            ->with('success', 'Inquilino registrado exitosamente.');
    }

    /**
     * Mostrar detalles del inquilino
     */
    public function show(Inquilino $inquilino)
    {
        return view('inquilinos.show', compact('inquilino'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Inquilino $inquilino)
    {
        return view('inquilinos.edit', compact('inquilino'));
    }

    /**
     * Actualizar información del inquilino
     */
    public function update(Request $request, Inquilino $inquilino)
    {
        $request->validate([
            'nombre_inquilino' => 'required|string|max:255',
            'nro_apartamento' => 'required|string|max:50|unique:inquilinos,nro_apartamento,' . $inquilino->id,
            'monto_deuda' => 'required|numeric|min:0',
            'monto_ultimo_pago' => 'nullable|numeric|min:0'
        ]);

        $inquilino->nombre_inquilino = $request->nombre_inquilino;
        $inquilino->nro_apartamento = $request->nro_apartamento;
        $inquilino->monto_deuda = $request->monto_deuda;
        
        // Si se actualiza el monto del último pago, actualizar fecha
        if ($request->monto_ultimo_pago != $inquilino->monto_ultimo_pago) {
            $inquilino->monto_ultimo_pago = $request->monto_ultimo_pago;
            $inquilino->fecha_ultimo_pago = Carbon::now();
        }
        
        $inquilino->save();

        return redirect()->route('inquilinos.index')
            ->with('success', 'Información del inquilino actualizada exitosamente.');
    }

    /**
     * Procesar pago del inquilino
     */
    public function procesarPago(Request $request, Inquilino $inquilino)
    {
        $request->validate([
            'monto_pago' => 'required|numeric|min:0.01'
        ]);

        $montoPago = $request->monto_pago;
        $deudaAnterior = $inquilino->monto_deuda;
        
        // Actualizar pago y recalcular deuda
        $inquilino->actualizarPago($montoPago);
        
        $mensaje = "Pago de $" . number_format($montoPago, 2) . " procesado exitosamente. ";
        $mensaje .= "Deuda anterior: $" . number_format($deudaAnterior, 2) . ". ";
        $mensaje .= "Deuda actual: $" . number_format($inquilino->monto_deuda, 2) . ".";

        return redirect()->route('inquilinos.show', $inquilino)
            ->with('success', $mensaje);
    }

    /**
     * Eliminar inquilino
     */
    public function destroy(Inquilino $inquilino)
    {
        $inquilino->delete();
        return redirect()->route('inquilinos.index')
            ->with('success', 'Inquilino eliminado exitosamente.');
    }

    /**
     * Show the import form for inquilinos.
     */
    public function showImport()
    {
        return view('inquilinos.import');
    }

    /**
     * Process the import of inquilinos from CSV file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $data = array_map('str_getcsv', file($path));
        
        // Remove header row
        $header = array_shift($data);
        
        $imported = 0;
        $errors = [];
        
        foreach ($data as $index => $row) {
            try {
                if (count($row) >= 3) {
                    Inquilino::create([
                        'nombre_inquilino' => $row[0],
                        'nro_apartamento' => $row[1],
                        'monto_deuda' => $row[2],
                        'monto_ultimo_pago' => $row[3] ?? 0,
                        'fecha_ultimo_pago' => !empty($row[4]) ? $row[4] : null
                    ]);
                    $imported++;
                }
            } catch (\Exception $e) {
                $errors[] = 'Error en fila ' . ($index + 2) . ': ' . $e->getMessage();
            }
        }
        
        $message = "Se importaron $imported inquilinos exitosamente.";
        if (!empty($errors)) {
            $message .= ' Errores: ' . implode(', ', $errors);
        }
        
        return redirect()->route('inquilinos.index')->with('success', $message);
    }

    /**
     * Download CSV template for inquilinos import.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="plantilla_inquilinos.csv"'
        ];
        
        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['nombre_inquilino', 'nro_apartamento', 'monto_deuda', 'monto_ultimo_pago', 'fecha_ultimo_pago']);
            fputcsv($file, ['Juan Pérez', 'APT-101', '1500.00', '500.00', '2024-01-15']);
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}