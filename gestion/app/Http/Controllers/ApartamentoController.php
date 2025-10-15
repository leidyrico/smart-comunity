<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Apartamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApartamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Cargar apartamentos con sus relaciones necesarias para calcular saldo pendiente
        $query = Apartamento::with(['pagos.reciboGastoComun'])
            ->orderBy('piso')
            ->orderBy('numero');
        
        // Si es usuario propietario, solo mostrar su propio apartamento
        $user = Auth::user();
        if ($user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario()) {
            if ($user->apartamento_id) {
                $query->where('id', $user->apartamento_id);
            } else {
                // Sin apartamento asociado, no mostrar resultados
                $query->whereRaw('1 = 0');
            }
        }
        
        // Filtro por estatus financiero si se proporciona
        if ($request->filled('estatus_financiero')) {
            $query->where('estatus_financiero', $request->estatus_financiero);
        }
        
        $apartamentos = $query->get();
        
        // Actualizar estatus financiero para cada apartamento basado en recibos asignados
        foreach ($apartamentos as $apartamento) {
            $apartamento->actualizarEstatusFinanciero();
        }
        
        return view('apartamentos.index', compact('apartamentos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Propietarios no pueden crear apartamentos
        $user = Auth::user();
        if ($user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario()) {
            abort(403);
        }
        return view('apartamentos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Propietarios no pueden crear apartamentos
        $user = Auth::user();
        if ($user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario()) {
            abort(403);
        }
        $request->validate([
            'numero' => 'required|string|max:20|unique:apartamentos,numero',
            'piso' => 'required|integer|min:0',
            'torre' => 'nullable|string|max:10',
            'propietario' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'area_m2' => 'nullable|numeric|min:0',
            'tipo' => 'required|in:apartamento,local,parqueadero,deposito,estudio',
            'estado' => 'required|in:ocupado,desocupado,en_arriendo',
            'observaciones' => 'nullable|string|max:1000'
        ]);

        Apartamento::create($request->all());

        return redirect()->route('apartamentos.index')
            ->with('success', 'Apartamento registrado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Apartamento $apartamento)
    {
        // Propietario solo puede ver su propio apartamento
        $user = Auth::user();
        if ($user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario() && $user->apartamento_id && $user->apartamento_id !== $apartamento->id) {
            abort(403);
        }
        $apartamento->load(['recibos', 'pagos']);
        return view('apartamentos.show', compact('apartamento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Apartamento $apartamento)
    {
        // Propietarios no pueden editar apartamentos
        $user = Auth::user();
        if ($user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario()) {
            abort(403);
        }
        return view('apartamentos.edit', compact('apartamento'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Apartamento $apartamento)
    {
        // Propietarios no pueden actualizar apartamentos
        $user = Auth::user();
        if ($user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario()) {
            abort(403);
        }
        $request->validate([
            'numero' => 'required|string|max:20|unique:apartamentos,numero,' . $apartamento->id,
            'piso' => 'required|integer|min:0',
            'torre' => 'nullable|string|max:10',
            'propietario' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'area_m2' => 'nullable|numeric|min:0',
            'tipo' => 'required|in:apartamento,local,parqueadero,deposito',
            'estado' => 'required|in:ocupado,desocupado,en_arriendo',
            'observaciones' => 'nullable|string|max:1000'
        ]);

        $apartamento->update($request->all());

        return redirect()->route('apartamentos.index')
            ->with('success', 'Apartamento actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Apartamento $apartamento)
    {
        // Propietarios no pueden eliminar apartamentos
        $user = Auth::user();
        if ($user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario()) {
            abort(403);
        }
        // Validar clave de administrador
        $adminPassword = $request->input('admin_password');
        $configuredPassword = config('app.admin_password', 'admin123'); // Clave por defecto
        
        if (!$adminPassword || $adminPassword !== $configuredPassword) {
            return redirect()->route('apartamentos.index')
                ->with('error', 'Clave de administrador incorrecta. No se pudo eliminar el apartamento.');
        }
        
        $numeroApartamento = $apartamento->numero;
        $apartamento->delete();
        
        return redirect()->route('apartamentos.index')
            ->with('success', "Apartamento {$numeroApartamento} eliminado exitosamente.");
    }

    /**
     * Mostrar formulario de importación CSV
     */
    public function showImport()
    {
        return view('apartamentos.import');
    }

    /**
     * Procesar importación CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
            'borrar_datos' => 'nullable|boolean'
        ]);

        $file = $request->file('csv_file');
        $csvData = array_map('str_getcsv', file($file->getRealPath()));
        $header = array_shift($csvData);
        
        // Limpiar encabezados de espacios y caracteres especiales
        $header = array_map('trim', $header);
        $header = array_map(function($col) { return preg_replace('/[^\w]/', '', $col); }, $header);

        // Validar que el CSV tenga las columnas requeridas
        $requiredColumns = ['numero', 'piso', 'torre', 'propietario', 'telefono', 'email', 'aream2', 'tipo', 'estado', 'observaciones'];
        $missingColumns = array_diff($requiredColumns, $header);
        
        if (!empty($missingColumns)) {
            return redirect()->back()
                ->withErrors(['csv_file' => 'El archivo CSV debe contener las columnas: ' . implode(', ', $requiredColumns)]);
        }

        // Si se seleccionó borrar datos, eliminar todos los apartamentos
        if ($request->has('borrar_datos') && $request->borrar_datos) {
            Apartamento::truncate();
        }

        $importedCount = 0;
        $errors = [];

        foreach ($csvData as $rowIndex => $row) {
            if (count($row) !== count($header)) {
                continue; // Saltar filas con número incorrecto de columnas
            }

            $data = array_combine($header, $row);
            
            // Validar datos requeridos
            if (empty($data['numero']) || empty($data['propietario'])) {
                $errors[] = "Fila " . ($rowIndex + 2) . ": Número y propietario son requeridos";
                continue;
            }

            // Verificar si ya existe el apartamento
            if (Apartamento::where('numero', $data['numero'])->exists()) {
                $errors[] = "Fila " . ($rowIndex + 2) . ": El apartamento {$data['numero']} ya existe";
                continue;
            }

            try {
                Apartamento::create([
                    'numero' => $data['numero'],
                    'piso' => (int)($data['piso'] ?? 1),
                    'torre' => $data['torre'] ?? null,
                    'propietario' => $data['propietario'],
                    'telefono' => $data['telefono'] ?? null,
                    'email' => $data['email'] ?? null,
                    'area_m2' => !empty($data['aream2']) ? (float)$data['aream2'] : null,
                    'tipo' => in_array($data['tipo'], ['apartamento', 'local', 'parqueadero', 'deposito']) ? $data['tipo'] : 'apartamento',
                    'estado' => in_array($data['estado'], ['ocupado', 'desocupado', 'en_arriendo']) ? $data['estado'] : 'ocupado',
                    'estatus_financiero' => 'solvente',
                    'observaciones' => $data['observaciones'] ?? null
                ]);
                $importedCount++;
            } catch (\Exception $e) {
                $errors[] = "Fila " . ($rowIndex + 2) . ": Error al crear apartamento - " . $e->getMessage();
            }
        }

        $message = "Se importaron {$importedCount} apartamentos exitosamente.";
        if (!empty($errors)) {
            $message .= " Se encontraron " . count($errors) . " errores.";
        }

        return redirect()->route('apartamentos.index')
            ->with('success', $message)
            ->with('import_errors', $errors);
    }

    /**
     * Descargar plantilla CSV
     */
    public function downloadTemplate()
    {
        $filename = 'plantilla_apartamentos.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Escribir BOM para UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            
            // Encabezados
            fputcsv($file, [
                'numero',
                'piso', 
                'torre',
                'propietario',
                'telefono',
                'email',
                'area_m2',
                'tipo',
                'estado',
                'observaciones'
            ]);
            
            // Ejemplos con diferentes tipos de apartamentos
            $ejemplos = [
                ['101', '1', 'A', 'Juan Pérez García', '3001234567', 'juan.perez@email.com', '65.5', 'apartamento', 'ocupado', 'Apartamento con balcón'],
                ['102', '1', 'A', 'María González López', '3007654321', 'maria.gonzalez@email.com', '58.2', 'apartamento', 'en_arriendo', 'Apartamento recién remodelado'],
                ['201', '2', 'A', 'Carlos Rodríguez Silva', '3009876543', 'carlos.rodriguez@email.com', '72.3', 'apartamento', 'ocupado', 'Apartamento con vista panorámica'],
                ['202', '2', 'A', 'Ana Martínez Ruiz', '3005432109', 'ana.martinez@email.com', '68.7', 'apartamento', 'desocupado', 'Apartamento disponible para arriendo'],
                ['301', '3', 'B', 'Luis Fernando Castro', '3002468135', 'luis.castro@email.com', '75.1', 'apartamento', 'ocupado', 'Penthouse con terraza'],
                ['P01', 'SS', 'A', 'Roberto Jiménez Vargas', '3001357924', 'roberto.jimenez@email.com', '12.0', 'parqueadero', 'ocupado', 'Parqueadero cubierto nivel sótano'],
                ['P02', 'SS', 'A', 'Carmen Elena Herrera', '3009753186', 'carmen.herrera@email.com', '12.0', 'parqueadero', 'ocupado', 'Parqueadero descubierto'],
                ['L01', '1', 'B', 'Diego Alejandro Vega', '3004567890', 'diego.vega@email.com', '45.0', 'local', 'en_arriendo', 'Local comercial con vitrina'],
                ['D01', 'SS', 'A', 'Patricia Isabel Rojas', '3006789012', 'patricia.rojas@email.com', '8.5', 'deposito', 'ocupado', 'Depósito en sótano nivel -1']
            ];
            
            foreach ($ejemplos as $ejemplo) {
                fputcsv($file, $ejemplo);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * API: Obtener lista de apartamentos para selección
     */
    public function getApartamentosApi()
    {
        $apartamentos = Apartamento::select('id', 'numero', 'propietario', 'estatus_financiero')
            ->orderBy('numero')
            ->get();
            
        return response()->json($apartamentos);
    }


}
