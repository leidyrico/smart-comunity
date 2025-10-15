<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\ReporteDeudas;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Incluir configuración de timeout para evitar errores de tiempo de ejecución
require_once __DIR__ . '/../../../config_timeout.php';

class DeudaController extends Controller
{
    /**
     * Mostrar el resumen de deudas por apartamento
     * Solo mostrar apartamentos que tengan recibos asignados y pagos asociados (actuales o históricos)
     */
    public function index(Request $request)
    {
        $query = Apartamento::with(['pagos', 'pagos.reciboGastoComun'])
            ->whereHas('pagos'); // Solo apartamentos que tienen o han tenido pagos (recibos asignados)

        // Filtro por número de apartamento si se proporciona (búsqueda exacta)
        if ($request->filled('numero_apartamento')) {
            $query->where('numero', $request->numero_apartamento);
        }

        // Filtro por propietario si se proporciona
        if ($request->filled('nombre_propietario')) {
            $query->where('propietario', 'like', '%' . $request->nombre_propietario . '%');
        }

        // Restringir a propietarios: solo su propio apartamento por email o apartamento_id
        $user = Auth::user();
        if ($user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario()) {
            if (!empty($user->apartamento_id)) {
                $query->where('id', $user->apartamento_id);
            } elseif (!empty($user->email)) {
                $query->where('email', $user->email);
            }
        }

        $apartamentos = $query->get();
        
        // Ordenamiento personalizado: PB-1 y PB-4 primero, luego resto ascendente, 144 al final
        $apartamentos = $apartamentos->sort(function($a, $b) {
            $numeroA = $a->numero;
            $numeroB = $b->numero;
            
            // PB-1 y PB-4 van primero
            if ($numeroA === 'PB-1') return -1;
            if ($numeroB === 'PB-1') return 1;
            if ($numeroA === 'PB-4') return -1;
            if ($numeroB === 'PB-4') return 1;
            
            // 144 va al final
            if ($numeroA === '144') return 1;
            if ($numeroB === '144') return -1;
            
            // Para el resto, ordenar numéricamente
            $numA = is_numeric($numeroA) ? (int)$numeroA : PHP_INT_MAX;
            $numB = is_numeric($numeroB) ? (int)$numeroB : PHP_INT_MAX;
            
            return $numA <=> $numB;
        });
        
        // Actualizar el estatus financiero de todos los apartamentos antes de mostrar los datos
        foreach ($apartamentos as $apartamento) {
            $apartamento->actualizarEstatusFinanciero();
        }

        // Obtener todos los recibos que tienen pagos asociados (asignados)
        // Incluir recibos que han tenido pagos aunque hayan sido eliminados
        // Ordenar todos los recibos de forma descendente por fecha de facturación
        $recibos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])
            ->whereHas('pagos') // Cualquier recibo que tenga pagos asociados
            ->orderBy('fecha_emision', 'desc')
            ->get();

        // Preparar datos detallados para la tabla
        $datosDeuda = [];
        
        foreach ($apartamentos as $apartamento) {
            foreach ($recibos as $recibo) {
                // Verificar si hay pagos asociados a este recibo para este apartamento
                $pagosAsociados = $apartamento->pagos
                    ->where('recibo_gasto_comun_id', $recibo->id);
                
                // Solo procesar si hay pagos asociados
                if ($pagosAsociados->isEmpty()) {
                    continue;
                }
                
                // Buscar pagos confirmados de este apartamento para este recibo (todos los tipos de pagos)
                $pagosConfirmados = $apartamento->pagos
                    ->where('recibo_gasto_comun_id', $recibo->id)
                    ->where('estado', 'confirmado');
                
                $montoPagado = $pagosConfirmados->sum('monto_pagado');
                $saldoActual = $recibo->total_recibo - $montoPagado;
                
                // Obtener la fecha del último pago para este recibo
                $ultimoPago = $pagosConfirmados->sortByDesc('fecha_pago')->first();
                $fechaPago = $ultimoPago ? $ultimoPago->fecha_pago : null;
                
                // Obtener todos los pagos confirmados para poder eliminarlos individualmente
                $pagosArray = $pagosConfirmados->map(function($pago) {
                    return [
                        'id' => $pago->id,
                        'monto' => $pago->monto_pagado,
                        'fecha' => $pago->fecha_pago,
                        'metodo' => $pago->metodo_pago
                    ];
                })->toArray();
                
                // Incluir todos los registros (con y sin deuda) para permitir ver historial completo
                $datosDeuda[] = [
                    'apartamento_id' => $apartamento->id,
                    'recibo_id' => $recibo->id,
                    'nombre_propietario' => $apartamento->propietario,
                    'numero_apartamento' => $apartamento->numero,
                    'numero_recibo' => $recibo->numero_recibo,
                    'fecha_facturacion' => $recibo->fecha_emision,
                    'monto_facturado' => $recibo->total_recibo,
                    'monto_pagado' => $montoPagado,
                    'fecha_pago' => $fechaPago,
                    'saldo_actual' => $saldoActual,
                    'tiene_deuda' => $saldoActual > 0,
                    'esta_vencido' => $recibo->estaVencido() && $saldoActual > 0,
                    'pagos' => $pagosArray
                ];
            }
        }

        // Convertir a colección y aplicar filtros adicionales
        $datosDeudaCollection = collect($datosDeuda);
        
        // Filtro por estado de deuda
        if ($request->filled('estado_deuda')) {
            if ($request->estado_deuda === 'pendiente') {
                $datosDeudaCollection = $datosDeudaCollection->where('saldo_actual', '>', 0);
            } elseif ($request->estado_deuda === 'pagado') {
                $datosDeudaCollection = $datosDeudaCollection->where('saldo_actual', '<=', 0);
            }
        } else {
            // Si se busca un apartamento específico, mostrar todo su historial (pendiente y pagado)
            if ($request->filled('numero_apartamento')) {
                // No aplicar filtro adicional, mostrar todo el historial del apartamento
            } else {
                // Por defecto, mostrar solo apartamentos con deuda pendiente (comportamiento original)
                $datosDeudaCollection = $datosDeudaCollection->where('saldo_actual', '>', 0);
            }
        }
        
        // Filtro por número de recibo
        if ($request->filled('numero_recibo')) {
            $datosDeudaCollection = $datosDeudaCollection->filter(function ($item) use ($request) {
                return stripos($item['numero_recibo'], $request->numero_recibo) !== false;
            });
        }
        $perPage = 15; // Registros por página
        $currentPage = request()->get('page', 1);
        $currentItems = $datosDeudaCollection->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $paginatedData = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $datosDeudaCollection->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
        
        // Mantener parámetros de filtro en la paginación
        $paginatedData->appends(request()->query());
        
        // Calcular estadísticas totales
        $estadisticas = [
            'total_registros' => $datosDeudaCollection->count(),
            'con_saldo_pendiente' => $datosDeudaCollection->where('saldo_actual', '>', 0)->count(),
            'pagados' => $datosDeudaCollection->where('saldo_actual', '<=', 0)->count(),
            'saldo_total_pendiente' => $datosDeudaCollection->sum('saldo_actual')
        ];
        
        return view('deudas.index', [
            'datosDeuda' => $paginatedData,
            'estadisticas' => $estadisticas
        ]);
    }

    /**
     * Mostrar detalle de deuda de un apartamento específico
     */
    public function show($id)
    {
        $apartamento = Apartamento::with(['pagos.reciboGastoComun'])->findOrFail($id);
        
        // Restringir a propietarios: acceso solo a su propio apartamento
        $user = Auth::user();
        if ($user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario()) {
            if (!empty($user->apartamento_id)) {
                if ((int)$apartamento->id !== (int)$user->apartamento_id) {
                    abort(403);
                }
            } elseif (!empty($user->email)) {
                if (!empty($apartamento->email) && $apartamento->email !== $user->email) {
                    abort(403);
                }
            }
        }
        
        // Actualizar el estatus financiero del apartamento antes de mostrar los detalles
        $apartamento->actualizarEstatusFinanciero();
        
        // Obtener solo los recibos que tienen pagos asociados a este apartamento
        // Excluir pagos rechazados para no mostrar recibos desasignados
        $recibosConPagos = $apartamento->pagos
            ->where('estado', '!=', 'rechazado')
            ->pluck('recibo_gasto_comun_id')
            ->unique();
        
        $recibos = ReciboGastoComun::whereIn('id', $recibosConPagos)
            ->whereIn('estado', ['activo', 'vencido'])
            ->orderBy('fecha_emision', 'desc')
            ->get();
        
        $detalleRecibos = [];
        
        foreach ($recibos as $recibo) {
            // Solo considerar pagos confirmados (excluir rechazados)
            $pagosTotales = $apartamento->pagos
                ->where('recibo_gasto_comun_id', $recibo->id)
                ->where('estado', 'confirmado')
                ->sum('monto_pagado');
            
            $saldoPendiente = $recibo->total_recibo - $pagosTotales;
            
            $detalleRecibos[] = [
                'recibo' => $recibo,
                'pagos_realizados' => $pagosTotales,
                'saldo_pendiente' => $saldoPendiente,
                'esta_pagado' => $saldoPendiente <= 0,
                'esta_vencido' => $recibo->estaVencido() && $saldoPendiente > 0
            ];
        }
        
        return view('deudas.show', compact('apartamento', 'detalleRecibos'));
    }

    /**
     * Cambiar el estado de un recibo específico
     */
    public function cambiarEstadoRecibo(Request $request, $reciboId)
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario()) {
            abort(403);
        }

        $request->validate([
            'estado' => 'required|in:pagado,pendiente'
        ]);

        $recibo = ReciboGastoComun::findOrFail($reciboId);
        $recibo->estado = $request->estado;
        $recibo->save();

        // Actualizar el estatus financiero de todos los apartamentos afectados
        $apartamentos = Apartamento::all();
        foreach ($apartamentos as $apartamento) {
            $apartamento->actualizarEstatusFinanciero();
        }

        return redirect()->back()->with('success', 'Estado del recibo actualizado correctamente.');
    }

    /**
     * Obtener resumen estadístico de deudas
     */
    public function estadisticas()
    {
        $totalApartamentos = Apartamento::count();
        $apartamentosConDeuda = 0;
        $deudaTotal = 0;
        
        $apartamentos = Apartamento::with(['pagos'])->get();
        $recibos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])->get();
        
        foreach ($apartamentos as $apartamento) {
            $deudaApartamento = 0;
            
            foreach ($recibos as $recibo) {
                $pagosTotales = $apartamento->pagos
                    ->where('recibo_gasto_comun_id', $recibo->id)
                    ->where('estado', 'confirmado')
                    ->sum('monto_pagado');
                
                $saldoPendiente = $recibo->total_recibo - $pagosTotales;
                
                if ($saldoPendiente > 0) {
                    $deudaApartamento += $saldoPendiente;
                }
            }
            
            if ($deudaApartamento > 0) {
                $apartamentosConDeuda++;
                $deudaTotal += $deudaApartamento;
            }
        }
        
        return response()->json([
            'total_apartamentos' => $totalApartamentos,
            'apartamentos_con_deuda' => $apartamentosConDeuda,
            'apartamentos_al_dia' => $totalApartamentos - $apartamentosConDeuda,
            'deuda_total' => $deudaTotal,
            'promedio_deuda' => $apartamentosConDeuda > 0 ? $deudaTotal / $apartamentosConDeuda : 0
        ]);
    }

    public function showImport()
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario()) {
            abort(403);
        }
        return view('deudas.import');
    }

    public function import(Request $request)
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario()) {
            abort(403);
        }
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $data = array_map('str_getcsv', file($path));
        
        // Remover la primera fila si contiene headers
        $headers = array_shift($data);
        
        $errors = [];
        $imported = 0;
        
        foreach ($data as $index => $row) {
            try {
                if (count($row) < 3) {
                    $errors[] = "Fila " . ($index + 2) . ": Datos insuficientes";
                    continue;
                }
                
                $apartamento = Apartamento::firstOrCreate(
                    ['numero' => $row[0]],
                    [
                        'propietario' => $row[1] ?? '',
                        'telefono' => $row[2] ?? '',
                        'email' => $row[3] ?? '',
                        'estatus_financiero' => 'solvente'
                    ]
                );
                
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Fila " . ($index + 2) . ": " . $e->getMessage();
            }
        }
        
        $message = "Se importaron {$imported} apartamentos.";
        if (!empty($errors)) {
            $message .= " Errores: " . implode(', ', $errors);
        }
        
        return redirect()->route('deudas.index')->with('success', $message);
    }

    public function downloadTemplate()
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario()) {
            abort(403);
        }
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="plantilla_apartamentos.csv"',
        ];
        
        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Numero', 'Propietario', 'Telefono', 'Email']);
            fputcsv($file, ['101', 'Juan Pérez', '555-1234', 'juan@email.com']);
            fputcsv($file, ['102', 'María García', '555-5678', 'maria@email.com']);
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Mostrar formulario de importación completa Excel
     */
    public function showImportCompleto()
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario()) {
            abort(403);
        }
        return view('deudas.import-completo');
    }

    /**
     * Procesar importación completa desde Excel
     */
    public function importCompleto(Request $request)
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario()) {
            abort(403);
        }
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240',
            'borrar_datos' => 'nullable|boolean',
            'validar_relaciones' => 'nullable|boolean',
            'continuar_errores' => 'nullable|boolean'
        ]);

        try {
            $file = $request->file('excel_file');
            
            // Logging detallado para diagnóstico
            \Log::info('Iniciando importación Excel', [
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'path' => $file->getRealPath()
            ]);
            
            // Verificar que el archivo existe y es legible
            if (!file_exists($file->getRealPath())) {
                throw new \Exception('El archivo no existe en la ruta temporal');
            }
            
            if (!is_readable($file->getRealPath())) {
                throw new \Exception('El archivo no es legible');
            }
            
            // Verificar extensiones requeridas
            if (!class_exists('ZipArchive')) {
                throw new \Exception('La clase ZipArchive no está disponible. Verifique que la extensión zip esté habilitada.');
            }
            
            \Log::info('Intentando cargar archivo Excel con IOFactory');
            $spreadsheet = IOFactory::load($file->getRealPath());
            \Log::info('Archivo Excel cargado exitosamente');
            
            $errors = [];
            $importedData = [
                'apartamentos' => 0,
                'recibos' => 0,
                'pagos' => 0
            ];

            // Si se seleccionó borrar datos, limpiar tablas
            if ($request->has('borrar_datos') && $request->borrar_datos) {
                // Desactivar verificaciones de claves foráneas temporalmente
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                
                // Truncar en orden correcto (primero las tablas dependientes)
                DB::table('pagos')->truncate();
                DB::table('recibo_gasto_comuns')->truncate();
                DB::table('apartamentos')->truncate();
                
                // Reactivar verificaciones de claves foráneas
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }

            DB::beginTransaction();

            // 1. Importar Apartamentos
            if ($spreadsheet->getSheetByName('Apartamentos')) {
                $result = $this->importApartamentosFromSheet($spreadsheet->getSheetByName('Apartamentos'), $request->continuar_errores);
                $importedData['apartamentos'] = $result['imported'];
                $errors = array_merge($errors, $result['errors']);
            }

            // 2. Importar Recibos
            if ($spreadsheet->getSheetByName('Recibos')) {
                $result = $this->importRecibosFromSheet($spreadsheet->getSheetByName('Recibos'), $request->continuar_errores, $request->validar_relaciones);
                $importedData['recibos'] = $result['imported'];
                $errors = array_merge($errors, $result['errors']);
            }

            // 3. Importar Pagos
            if ($spreadsheet->getSheetByName('Pagos')) {
                $result = $this->importPagosFromSheet($spreadsheet->getSheetByName('Pagos'), $request->continuar_errores, $request->validar_relaciones);
                $importedData['pagos'] = $result['imported'];
                $errors = array_merge($errors, $result['errors']);
            }

            // NO recalcular estatus financiero automáticamente - respetar el estatus definido en Excel
            // $this->recalcularEstatusFinancieroTodosApartamentos();

            DB::commit();

            $message = "Importación completada: {$importedData['apartamentos']} apartamentos, {$importedData['recibos']} recibos, {$importedData['pagos']} pagos.";
            if (!empty($errors)) {
                $message .= " Se encontraron " . count($errors) . " errores.";
            }

            // Si hay errores, redirigir a la página de errores detallados
            if (!empty($errors)) {
                return redirect()->route('deudas.import.errors')
                    ->with('success', $message)
                    ->with('import_errors', $errors)
                    ->with('import_summary', $importedData);
            }
            
            return redirect()->route('deudas.index')
                ->with('success', $message)
                ->with('import_summary', $importedData);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Logging detallado del error
            \Log::error('Error en importación Excel', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMessage = 'Error al procesar el archivo Excel: ' . $e->getMessage();
            
            // Agregar información específica según el tipo de error
            if (strpos($e->getMessage(), 'ZipArchive') !== false) {
                $errorMessage .= ' (Problema con la extensión ZIP de PHP)';
            } elseif (strpos($e->getFile(), 'PhpSpreadsheet') !== false) {
                $errorMessage .= ' (Error en PhpSpreadsheet - línea ' . $e->getLine() . ')';
            }
            
            return redirect()->back()
                ->withErrors(['excel_file' => $errorMessage])
                ->withInput();
        }
    }

    /**
     * Importar apartamentos desde hoja de Excel
     */
    private function importApartamentosFromSheet($sheet, $continueOnError = false)
    {
        $data = $sheet->toArray();
        $header = array_shift($data);
        $imported = 0;
        $errors = [];

        $requiredColumns = ['numero', 'propietario'];
        $missingColumns = array_diff($requiredColumns, $header);
        
        if (!empty($missingColumns)) {
            $errors[] = 'Hoja Apartamentos: Faltan columnas requeridas: ' . implode(', ', $missingColumns);
            return ['imported' => 0, 'errors' => $errors];
        }

        foreach ($data as $rowIndex => $row) {
            if (count($row) !== count($header) || empty($row[0])) {
                continue;
            }

            $rowData = array_combine($header, $row);

            try {
                Apartamento::firstOrCreate(
                    ['numero' => $rowData['numero']],
                    [
                        'propietario' => $rowData['propietario'] ?? '',
                        'telefono' => $rowData['telefono'] ?? '',
                        'email' => $rowData['email'] ?? '',
                        'piso' => $rowData['piso'] ?? null,
                        'torre' => $rowData['torre'] ?? null,
                        'area_m2' => $rowData['area_m2'] ?? null,
                        'tipo' => $rowData['tipo'] ?? 'apartamento',
                        'estado' => $rowData['estado'] ?? 'ocupado',
                        'estatus_financiero' => in_array(strtolower($rowData['estatus_financiero'] ?? ''), ['solvente', 'deudor', 'moroso']) 
                            ? strtolower($rowData['estatus_financiero']) 
                            : 'solvente',
                        'observaciones' => $rowData['observaciones'] ?? ''
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $error = "Apartamento fila " . ($rowIndex + 2) . ": " . $e->getMessage();
                $errors[] = $error;
                
                // Log detallado del error
                \Log::error('Error importando apartamento', [
                    'fila' => $rowIndex + 2,
                    'datos' => $rowData,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                if (!$continueOnError) {
                    break;
                }
            }
        }

        return ['imported' => $imported, 'errors' => $errors];
    }

    /**
     * Parsear fecha desde diferentes formatos
     */
    private function parseFecha($fechaStr)
    {
        if (empty($fechaStr)) {
            return now();
        }
        
        // Intentar diferentes formatos de fecha
        $formatos = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y', 'd-m-y'];
        
        foreach ($formatos as $formato) {
            try {
                return Carbon::createFromFormat($formato, $fechaStr);
            } catch (\Exception $e) {
                continue;
            }
        }
        
        // Si ningún formato funciona, intentar parse automático
        try {
            return Carbon::parse($fechaStr);
        } catch (\Exception $e) {
            \Log::warning("No se pudo parsear la fecha: {$fechaStr}");
            return now();
        }
    }

    /**
     * Recalcular estatus financiero de todos los apartamentos basado en recibos activos/vencidos
     */
    private function recalcularEstatusFinancieroTodosApartamentos()
    {
        $apartamentos = Apartamento::all();
        
        foreach ($apartamentos as $apartamento) {
            $apartamento->actualizarEstatusFinanciero();
        }
        
        \Log::info('Estatus financiero recalculado para todos los apartamentos después de importación Excel');
    }

    /**
     * Mostrar página de errores detallados de importación
     */
    public function showImportErrors()
    {
        return view('deudas.import-errors');
    }

    /**
     * Importar recibos desde hoja de Excel
     */
    private function importRecibosFromSheet($sheet, $continueOnError = false, $validateRelations = false)
    {
        $data = $sheet->toArray();
        $header = array_shift($data);
        $imported = 0;
        $errors = [];

        $requiredColumns = ['numero_recibo', 'periodo', 'fecha_emision', 'fecha_vencimiento', 'valor_administracion', 'valor_aseo', 'valor_vigilancia', 'valor_mantenimiento', 'otros_conceptos', 'estado'];
        $missingColumns = array_diff($requiredColumns, $header);
        
        if (!empty($missingColumns)) {
            $errors[] = 'Hoja Recibos: Faltan columnas requeridas: ' . implode(', ', $missingColumns);
            return ['imported' => 0, 'errors' => $errors];
        }

        foreach ($data as $rowIndex => $row) {
            if (count($row) !== count($header) || empty($row[0]) || empty($row[1])) {
                continue;
            }

            $rowData = array_combine($header, $row);

            try {
                // Buscar apartamento por número de recibo si existe la columna
                $apartamento = null;
                if (isset($rowData['apartamento_numero'])) {
                    $apartamento = Apartamento::where('numero', $rowData['apartamento_numero'])->first();
                }
                
                $recibo = new ReciboGastoComun([
                    'numero_recibo' => $rowData['numero_recibo'],
                    'periodo' => $rowData['periodo'],
                    'fecha_emision' => $this->parseExcelDate($rowData['fecha_emision'] ?? null),
                    'fecha_vencimiento' => $this->parseExcelDate($rowData['fecha_vencimiento'] ?? null),
                    'valor_administracion' => $rowData['valor_administracion'] ?? 0,
                    'valor_aseo' => $rowData['valor_aseo'] ?? 0,
                    'valor_vigilancia' => $rowData['valor_vigilancia'] ?? 0,
                    'valor_mantenimiento' => $rowData['valor_mantenimiento'] ?? 0,
                    'otros_conceptos' => $rowData['otros_conceptos'] ?? 0,
                    'estado' => $rowData['estado'] ?? 'activo'
                ]);
                $recibo->calcularTotal();
                $recibo->save();
                
                // Si el recibo está activo, asignarlo a todos los apartamentos
                if ($recibo->estado === 'activo') {
                    $this->asignarReciboATodosApartamentos($recibo);
                }
                
                $imported++;
            } catch (\Exception $e) {
                $error = "Recibo fila " . ($rowIndex + 2) . ": " . $e->getMessage();
                $errors[] = $error;
                
                // Log detallado del error
                \Log::error('Error importando recibo', [
                    'fila' => $rowIndex + 2,
                    'datos' => $rowData,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                if (!$continueOnError) {
                    break;
                }
            }
        }

        return ['imported' => $imported, 'errors' => $errors];
    }

    /**
     * Importar pagos desde hoja de Excel
     */
    private function importPagosFromSheet($sheet, $continueOnError = false, $validateRelations = false)
    {
        $data = $sheet->toArray();
        $header = array_shift($data);
        $imported = 0;
        $errors = [];

        $requiredColumns = ['apartamento_numero', 'monto_pagado'];
        $missingColumns = array_diff($requiredColumns, $header);
        
        if (!empty($missingColumns)) {
            $errors[] = 'Hoja Pagos: Faltan columnas requeridas: ' . implode(', ', $missingColumns);
            return ['imported' => 0, 'errors' => $errors];
        }

        foreach ($data as $rowIndex => $row) {
            if (count($row) !== count($header) || empty($row[0]) || empty($row[1])) {
                continue;
            }

            $rowData = array_combine($header, $row);

            try {
                $apartamento = Apartamento::where('numero', $rowData['apartamento_numero'])->first();
                
                if (!$apartamento) {
                    if ($validateRelations) {
                        $error = "Pago fila " . ($rowIndex + 2) . ": Apartamento {$rowData['apartamento_numero']} no encontrado";
                        $errors[] = $error;
                        
                        if (!$continueOnError) {
                            break;
                        }
                        continue;
                    } else {
                        // Crear apartamento automáticamente
                        $apartamento = Apartamento::create([
                            'numero' => $rowData['apartamento_numero'],
                            'propietario' => 'Propietario por definir',
                            'telefono' => '',
                            'email' => ''
                        ]);
                    }
                }
                
                // Buscar recibo por número si se proporciona
                $recibo = null;
                if (isset($rowData['recibo_numero']) && $rowData['recibo_numero']) {
                    $recibo = ReciboGastoComun::where('numero_recibo', $rowData['recibo_numero'])->first();
                }
                
                Pago::create([
                    'apartamento_id' => $apartamento->id,
                    'recibo_gasto_comun_id' => $recibo ? $recibo->id : null,
                    'monto_pagado' => $rowData['monto_pagado'] ?? 0,
                    'fecha_pago' => isset($rowData['fecha_pago']) && $rowData['fecha_pago'] 
                        ? $this->parseExcelDate($rowData['fecha_pago']) 
                        : now(),
                    'metodo_pago' => $rowData['metodo_pago'] ?? 'efectivo',
                    'numero_comprobante' => $rowData['numero_comprobante'] ?? '',
                    'estado' => $rowData['estado'] ?? 'confirmado',
                    'observaciones' => $rowData['observaciones'] ?? ''
                ]);
                $imported++;
            } catch (\Exception $e) {
                $error = "Pago fila " . ($rowIndex + 2) . ": " . $e->getMessage();
                $errors[] = $error;
                
                // Log detallado del error
                \Log::error('Error importando pago', [
                    'fila' => $rowIndex + 2,
                    'datos' => $rowData,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                if (!$continueOnError) {
                    break;
                }
            }
        }

        return ['imported' => $imported, 'errors' => $errors];
    }

    /**
     * Descargar plantilla completa Excel
     */
    public function downloadTemplateCompleto()
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario()) {
            abort(403);
        }
        $spreadsheet = new Spreadsheet();
        
        // Hoja de Apartamentos
        $apartamentosSheet = $spreadsheet->getActiveSheet();
        $apartamentosSheet->setTitle('Apartamentos');
        $apartamentosSheet->setCellValue('A1', 'numero');
        $apartamentosSheet->setCellValue('B1', 'piso');
        $apartamentosSheet->setCellValue('C1', 'torre');
        $apartamentosSheet->setCellValue('D1', 'propietario');
        $apartamentosSheet->setCellValue('E1', 'telefono');
        $apartamentosSheet->setCellValue('F1', 'email');
        $apartamentosSheet->setCellValue('G1', 'area_m2');
        $apartamentosSheet->setCellValue('H1', 'tipo');
        $apartamentosSheet->setCellValue('I1', 'estado');
        $apartamentosSheet->setCellValue('J1', 'estatus_financiero');
        $apartamentosSheet->setCellValue('K1', 'observaciones');
        
        // Datos de ejemplo
        $apartamentosSheet->setCellValue('A2', '101');
        $apartamentosSheet->setCellValue('B2', '1');
        $apartamentosSheet->setCellValue('C2', 'A');
        $apartamentosSheet->setCellValue('D2', 'Juan Pérez García');
        $apartamentosSheet->setCellValue('E2', '3001234567');
        $apartamentosSheet->setCellValue('F2', 'juan.perez@email.com');
        $apartamentosSheet->setCellValue('G2', '65.5');
        $apartamentosSheet->setCellValue('H2', 'apartamento');
        $apartamentosSheet->setCellValue('I2', 'ocupado');
        $apartamentosSheet->setCellValue('J2', 'solvente');
        $apartamentosSheet->setCellValue('K2', 'Apartamento con balcón');
        
        $apartamentosSheet->setCellValue('A3', '102');
        $apartamentosSheet->setCellValue('B3', '1');
        $apartamentosSheet->setCellValue('C3', 'A');
        $apartamentosSheet->setCellValue('D3', 'María González López');
        $apartamentosSheet->setCellValue('E3', '3007654321');
        $apartamentosSheet->setCellValue('F3', 'maria.gonzalez@email.com');
        $apartamentosSheet->setCellValue('G3', '58.2');
        $apartamentosSheet->setCellValue('H3', 'apartamento');
        $apartamentosSheet->setCellValue('I3', 'en_arriendo');
        $apartamentosSheet->setCellValue('J3', 'deudor');
        $apartamentosSheet->setCellValue('K3', 'Apartamento recién remodelado');
        
        // Hoja de Recibos
        $recibosSheet = $spreadsheet->createSheet();
        $recibosSheet->setTitle('Recibos');
        $recibosSheet->setCellValue('A1', 'numero_recibo');
        $recibosSheet->setCellValue('B1', 'periodo');
        $recibosSheet->setCellValue('C1', 'fecha_emision');
        $recibosSheet->setCellValue('D1', 'fecha_vencimiento');
        $recibosSheet->setCellValue('E1', 'valor_administracion');
        $recibosSheet->setCellValue('F1', 'valor_aseo');
        $recibosSheet->setCellValue('G1', 'valor_vigilancia');
        $recibosSheet->setCellValue('H1', 'valor_mantenimiento');
        $recibosSheet->setCellValue('I1', 'otros_conceptos');
        $recibosSheet->setCellValue('J1', 'estado');
        
        // Datos de ejemplo
        $recibosSheet->setCellValue('A2', 'REC-2024-001');
        $recibosSheet->setCellValue('B2', '2024-01');
        $recibosSheet->setCellValue('C2', '01-01-2024');
        $recibosSheet->setCellValue('D2', '31-01-2024');
        $recibosSheet->setCellValue('E2', '120000');
        $recibosSheet->setCellValue('F2', '15000');
        $recibosSheet->setCellValue('G2', '25000');
        $recibosSheet->setCellValue('H2', '10000');
        $recibosSheet->setCellValue('I2', '5000');
        $recibosSheet->setCellValue('J2', 'activo');
        
        $recibosSheet->setCellValue('A3', 'REC-2024-002');
        $recibosSheet->setCellValue('B3', '2024-02');
        $recibosSheet->setCellValue('C3', '01-02-2024');
        $recibosSheet->setCellValue('D3', '29-02-2024');
        $recibosSheet->setCellValue('E3', '120000');
        $recibosSheet->setCellValue('F3', '15000');
        $recibosSheet->setCellValue('G3', '25000');
        $recibosSheet->setCellValue('H3', '10000');
        $recibosSheet->setCellValue('I3', '0');
        $recibosSheet->setCellValue('J3', 'activo');
        
        // Hoja de Pagos
        $pagosSheet = $spreadsheet->createSheet();
        $pagosSheet->setTitle('Pagos');
        $pagosSheet->setCellValue('A1', 'apartamento_numero');
        $pagosSheet->setCellValue('B1', 'recibo_numero');
        $pagosSheet->setCellValue('C1', 'fecha_pago');
        $pagosSheet->setCellValue('D1', 'monto_pagado');
        $pagosSheet->setCellValue('E1', 'metodo_pago');
        $pagosSheet->setCellValue('F1', 'numero_comprobante');
        $pagosSheet->setCellValue('G1', 'estado');
        $pagosSheet->setCellValue('H1', 'observaciones');
        
        // Datos de ejemplo
        $pagosSheet->setCellValue('A2', '101');
        $pagosSheet->setCellValue('B2', 'REC-2024-001');
        $pagosSheet->setCellValue('C2', '15-01-2024');
        $pagosSheet->setCellValue('D2', '175000');
        $pagosSheet->setCellValue('E2', 'transferencia');
        $pagosSheet->setCellValue('F2', 'TRF-001-2024');
        $pagosSheet->setCellValue('G2', 'confirmado');
        $pagosSheet->setCellValue('H2', 'Pago completo del período');
        
        $pagosSheet->setCellValue('A3', '102');
        $pagosSheet->setCellValue('B3', 'REC-2024-001');
        $pagosSheet->setCellValue('C3', '20-01-2024');
        $pagosSheet->setCellValue('D3', '100000');
        $pagosSheet->setCellValue('E3', 'efectivo');
        $pagosSheet->setCellValue('F3', 'EFE-002-2024');
        $pagosSheet->setCellValue('G3', 'confirmado');
        $pagosSheet->setCellValue('H3', 'Pago parcial');
        
        $writer = new Xlsx($spreadsheet);
        
        $fileName = 'plantilla_completa.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($temp_file);
        
        return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Parsear fechas de Excel que pueden venir en diferentes formatos
     */
    private function parseExcelDate($dateValue)
    {
        // Log del valor original para debugging
        \Log::info('parseExcelDate - Valor original', [
            'value' => $dateValue,
            'type' => gettype($dateValue),
            'is_numeric' => is_numeric($dateValue)
        ]);
        
        if (empty($dateValue)) {
            \Log::warning('parseExcelDate - Valor vacío, usando fecha actual');
            return now();
        }

        // Si es un número (fecha serial de Excel)
        if (is_numeric($dateValue)) {
            try {
                // Verificar que sea un número válido de fecha Excel (entre 1 y 2958465)
                if ($dateValue < 1 || $dateValue > 2958465) {
                    throw new \Exception("Número fuera del rango válido de fechas Excel: {$dateValue}");
                }
                
                $result = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue);
                \Log::info('parseExcelDate - Éxito con serial Excel', [
                    'serial' => $dateValue,
                    'fecha' => $result->format('d-m-Y')
                ]);
                return $result;
            } catch (\Exception $e) {
                \Log::warning('parseExcelDate - Error con serial Excel', [
                    'serial' => $dateValue,
                    'error' => $e->getMessage()
                ]);
                
                // Si falla, intentar como timestamp solo si es un número razonable
                if ($dateValue > 946684800 && $dateValue < 4102444800) { // Entre 2000 y 2100
                    try {
                        $result = Carbon::createFromTimestamp($dateValue);
                        \Log::info('parseExcelDate - Éxito con timestamp', [
                            'timestamp' => $dateValue,
                            'fecha' => $result->format('d-m-Y')
                        ]);
                        return $result;
                    } catch (\Exception $e2) {
                        \Log::warning('parseExcelDate - Error con timestamp', [
                            'timestamp' => $dateValue,
                            'error' => $e2->getMessage()
                        ]);
                    }
                }
            }
        }

        // Si es una cadena, usar formatos con prioridad al formato d-m-Y más común
        $formats = [
            'd-m-Y',   // 31-07-2023 (formato más usado según usuario)
            'Y-m-d',   // 2023-07-31 (ISO format - más confiable)
            'm/d/Y',   // 07/31/2023 (formato US - común en Excel)
            'm-d-Y',   // 07-31-2023
            'd/m/Y',   // 31/07/2023 (formato europeo común - después de americano para evitar ambigüedad)
            'Y/m/d',   // 2023/07/31
            'd/m/y',   // 31/07/23
            'd-m-y',   // 31-07-23
            'm/d/y',   // 07/31/23
            'm-d-y'    // 07-31-23
        ];

        // Detectar si es probable formato americano (m/d/Y) vs europeo (d/m/Y)
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $dateValue, $matches)) {
            $firstPart = (int)$matches[1];
            $secondPart = (int)$matches[2];
            
            // Si el primer número es <= 12 y el segundo > 12, probablemente es m/d/Y
            if ($firstPart <= 12 && $secondPart > 12) {
                $formats = array_merge(['m/d/Y'], array_diff($formats, ['m/d/Y']));
            }
            // Si el primer número > 12, definitivamente es d/m/Y
            elseif ($firstPart > 12) {
                $formats = array_merge(['d/m/Y'], array_diff($formats, ['d/m/Y']));
            }
        }

        // Solo usar formatos americanos - no necesitamos detección especial

        foreach ($formats as $format) {
            try {
                $result = Carbon::createFromFormat($format, $dateValue);
                
                // Validar que la fecha sea razonable (entre 2020 y 2030)
                if ($result->year < 2020 || $result->year > 2030) {
                    \Log::warning('parseExcelDate - Fecha fuera de rango razonable', [
                        'fecha_str' => $dateValue,
                        'formato' => $format,
                        'año' => $result->year
                    ]);
                    continue;
                }
                
                // Validación adicional: verificar que el parsing fue correcto
                // comparando la fecha original con la formateada
                $reformatted = $result->format($format);
                if ($reformatted !== $dateValue) {
                    \Log::warning('parseExcelDate - Fecha no coincide al reformatear', [
                        'fecha_str' => $dateValue,
                        'formato' => $format,
                        'reformateada' => $reformatted
                    ]);
                    continue;
                }
                
                \Log::info('parseExcelDate - Éxito con formato', [
                    'fecha_str' => $dateValue,
                    'formato' => $format,
                    'fecha' => $result->format('d-m-Y')
                ]);
                return $result;
            } catch (\Exception $e) {
                continue;
            }
        }

        // Si ningún formato funciona, intentar parse automático
        try {
            $result = Carbon::parse($dateValue);
            
            // Validar que la fecha sea razonable
            if ($result->year < 2020 || $result->year > 2030) {
                throw new \Exception("Fecha fuera de rango razonable: {$result->year}");
            }
            
            \Log::info('parseExcelDate - Éxito con parse automático', [
                'fecha_str' => $dateValue,
                'fecha' => $result->format('d-m-Y')
            ]);
            return $result;
        } catch (\Exception $e) {
            \Log::error('parseExcelDate - Todos los métodos fallaron', [
                'fecha_str' => $dateValue,
                'error' => $e->getMessage()
            ]);
            
            // Como último recurso, usar fecha actual
            return now();
        }
    }

    public function exportExcel(Request $request)
    {
        // Obtener los mismos datos que en el index
        $query = ReciboGastoComunModel::with(['apartamento', 'pagos'])
            ->select('recibo_gasto_comun.*')
            ->selectRaw('(recibo_gasto_comun.monto - COALESCE(SUM(pagos.monto), 0)) as saldo_pendiente')
            ->leftJoin('pagos', 'recibo_gasto_comun.id', '=', 'pagos.recibo_id')
            ->groupBy('recibo_gasto_comun.id');

        // Aplicar filtros si existen
        if ($request->filled('apartamento')) {
            $query->whereHas('apartamento', function($q) use ($request) {
                $q->where('numero', 'like', '%' . $request->apartamento . '%');
            });
        }

        if ($request->filled('mes')) {
            $query->whereMonth('fecha_emision', $request->mes);
        }

        if ($request->filled('anio')) {
            $query->whereYear('fecha_emision', $request->anio);
        }

        if ($request->filled('estado')) {
            if ($request->estado == 'pagado') {
                $query->havingRaw('saldo_pendiente <= 0');
            } elseif ($request->estado == 'pendiente') {
                $query->havingRaw('saldo_pendiente > 0');
            }
        }

        $recibos = $query->orderBy('fecha_emision', 'desc')->get();

        // Crear el archivo Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $headers = [
            'A1' => 'Apartamento',
            'B1' => 'Propietario', 
            'C1' => 'Estatus Financiero',
            'D1' => 'Fecha Emisión',
            'E1' => 'Mes',
            'F1' => 'Año',
            'G1' => 'Monto',
            'H1' => 'Pagado',
            'I1' => 'Saldo Pendiente',
            'J1' => 'Estado'
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        // Datos
        $row = 2;
        foreach ($recibos as $recibo) {
            // Actualizar estatus financiero del apartamento
            $recibo->apartamento->actualizarEstatusFinanciero();
            
            $sheet->setCellValue('A' . $row, $recibo->apartamento->numero);
            $sheet->setCellValue('B' . $row, $recibo->apartamento->propietario);
            $sheet->setCellValue('C' . $row, ucfirst($recibo->apartamento->estatus_financiero));
            $sheet->setCellValue('D' . $row, $recibo->fecha_emision->format('d/m/Y'));
            $sheet->setCellValue('E' . $row, $recibo->fecha_emision->format('m'));
            $sheet->setCellValue('F' . $row, $recibo->fecha_emision->format('Y'));
            $sheet->setCellValue('G' . $row, $recibo->monto);
            $sheet->setCellValue('H' . $row, $recibo->monto - $recibo->saldo_pendiente);
            $sheet->setCellValue('I' . $row, $recibo->saldo_pendiente);
            $sheet->setCellValue('J' . $row, $recibo->saldo_pendiente > 0 ? 'Pendiente' : 'Pagado');
            $row++;
        }

        // Ajustar ancho de columnas
        foreach (range('A', 'J') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Crear el writer y descargar
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        $filename = 'deudas_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
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
                'motivo' => 'Los recibos vencidos solo se asignan manualmente - Importación Excel'
            ]);
            return;
        }
        
        // Para recibos activos, asignar a todos los apartamentos
        $apartamentos = Apartamento::all();
        
        foreach ($apartamentos as $apartamento) {
            // Solo para recibos activos - determinar el estado del pago
            $estadoPago = 'pendiente_confirmacion';
            $observaciones = 'Recibo asignado automáticamente - Importación Excel';
            
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
     * Mostrar formulario de importación selectiva de recibos vencidos
     */
    public function showImportRecibosVencidos()
    {
        return view('deudas.import-recibos-vencidos');
    }

    /**
     * Procesar importación selectiva de recibos vencidos
     */
    public function importRecibosVencidos(Request $request)
    {
        $request->validate([
            'archivo_excel' => 'required|file|mimes:xlsx,xls',
            'apartamentos_seleccionados' => 'required|array|min:1',
            'apartamentos_seleccionados.*' => 'exists:apartamentos,id'
        ]);

        try {
            DB::beginTransaction();

            $file = $request->file('archivo_excel');
            $spreadsheet = IOFactory::load($file->getPathname());
            
            // Verificar que existe la hoja "Recibos"
            if (!$spreadsheet->getSheetByName('Recibos')) {
                throw new \Exception('El archivo debe contener una hoja llamada "Recibos"');
            }

            $worksheet = $spreadsheet->getSheetByName('Recibos');
            $data = $worksheet->toArray();
            
            if (empty($data)) {
                throw new \Exception('La hoja "Recibos" está vacía');
            }

            // Procesar encabezados
            $headers = array_map('trim', $data[0]);
            $requiredHeaders = ['numero_recibo', 'periodo', 'fecha_emision', 'fecha_vencimiento', 'valor_administracion', 'total_recibo', 'estado'];
            
            foreach ($requiredHeaders as $required) {
                if (!in_array($required, $headers)) {
                    throw new \Exception("Falta la columna requerida: {$required}");
                }
            }

            $apartamentosSeleccionados = $request->apartamentos_seleccionados;
            $recibosImportados = 0;
            $asignacionesCreadas = 0;
            $errores = [];

            // Procesar cada fila de recibos
            for ($i = 1; $i < count($data); $i++) {
                $row = $data[$i];
                if (empty(array_filter($row))) continue; // Saltar filas vacías

                $rowData = array_combine($headers, $row);
                
                try {
                    // Verificar si el recibo ya existe
                    $reciboExistente = ReciboGastoComun::where('numero_recibo', $rowData['numero_recibo'])->first();
                    
                    if ($reciboExistente) {
                        $errores[] = "Fila " . ($i + 1) . ": El recibo {$rowData['numero_recibo']} ya existe";
                        continue;
                    }

                    // Crear el recibo
                    $recibo = ReciboGastoComun::create([
                        'numero_recibo' => $rowData['numero_recibo'],
                        'periodo' => $rowData['periodo'],
                        'fecha_emision' => $this->parseDate($rowData['fecha_emision']),
                        'fecha_vencimiento' => $this->parseDate($rowData['fecha_vencimiento']),
                        'valor_administracion' => (float)($rowData['valor_administracion'] ?? 0),
                        'valor_aseo' => (float)($rowData['valor_aseo'] ?? 0),
                        'valor_vigilancia' => (float)($rowData['valor_vigilancia'] ?? 0),
                        'valor_mantenimiento' => (float)($rowData['valor_mantenimiento'] ?? 0),
                        'otros_conceptos' => (float)($rowData['otros_conceptos'] ?? 0),
                        'total_recibo' => (float)$rowData['total_recibo'],
                        'estado' => in_array($rowData['estado'], ['activo', 'vencido', 'anulado']) ? $rowData['estado'] : 'vencido',
                        'observaciones' => $rowData['observaciones'] ?? 'Importado selectivamente'
                    ]);

                    $recibosImportados++;

                    // Asignar solo a apartamentos seleccionados
                    foreach ($apartamentosSeleccionados as $apartamentoId) {
                        $apartamento = Apartamento::find($apartamentoId);
                        
                        if ($apartamento) {
                            // Determinar estado del pago basado en el estatus del apartamento
                            $estadoPago = 'pendiente_confirmacion';
                            if ($apartamento->estatus_financiero === 'deudor') {
                                $estadoPago = 'rechazado';
                            }

                            Pago::create([
                                'apartamento_id' => $apartamento->id,
                                'recibo_gasto_comun_id' => $recibo->id,
                                'monto_pagado' => 0,
                                'fecha_pago' => now(),
                                'metodo_pago' => 'pendiente',
                                'estado' => $estadoPago,
                                'observaciones' => 'Asignación selectiva de recibo vencido'
                            ]);

                            // Actualizar estatus financiero a deudor si el recibo está activo o vencido
                            if (in_array($recibo->estado, ['activo', 'vencido']) && $apartamento->estatus_financiero !== 'deudor') {
                                $apartamento->update([
                                    'estatus_financiero' => 'deudor',
                                    'fecha_cambio_estatus' => now()->toDateString()
                                ]);
                            }

                            $asignacionesCreadas++;
                        }
                    }

                } catch (\Exception $e) {
                    $errores[] = "Fila " . ($i + 1) . ": " . $e->getMessage();
                }
            }

            DB::commit();

            $mensaje = "Importación completada: {$recibosImportados} recibos importados, {$asignacionesCreadas} asignaciones creadas";
            
            if (!empty($errores)) {
                $mensaje .= ". Errores encontrados: " . count($errores);
                session(['import_errors' => $errores]);
            }

            return redirect()->route('deudas.import.recibos-vencidos')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('deudas.import.recibos-vencidos')
                ->with('error', 'Error durante la importación: ' . $e->getMessage());
        }
    }

    /**
     * Enviar reporte de deudas por correo
     */
    public function enviarReportePorCorreo(Request $request)
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario()) {
            abort(403);
        }
        try {
            \Log::info('=== INICIO ENVIO CORREO ===');
            \Log::info('Request data:', $request->all());
            
            // Obtener los mismos datos que se muestran en la vista
            $query = Apartamento::with(['pagos', 'pagos.reciboGastoComun'])
                ->whereHas('pagos')
                ->orderBy('numero');

            // Aplicar filtros
            if ($request->filled('numero_apartamento')) {
                \Log::info('Filtro apartamento aplicado:', ['numero' => $request->numero_apartamento]);
                $query->where('numero', $request->numero_apartamento);
            }

            if ($request->filled('nombre_propietario')) {
                \Log::info('Filtro propietario aplicado:', ['nombre' => $request->nombre_propietario]);
                $query->where('propietario', 'like', '%' . $request->nombre_propietario . '%');
            }

            $apartamentos = $query->get();
            \Log::info('Apartamentos encontrados:', ['count' => $apartamentos->count()]);
            
            // Actualizar el estatus financiero
            foreach ($apartamentos as $apartamento) {
                $apartamento->actualizarEstatusFinanciero();
            }

            // Obtener recibos que están asignados (manual, automáticamente o por pagos globales)
            // Ordenar todos los recibos de forma descendente por fecha de facturación
            $recibos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])
                ->whereHas('pagos', function($query) {
                    $query->where(function($subQuery) {
                        $subQuery->where('observaciones', 'like', '%Asignación manual%')
                                 ->orWhere('observaciones', 'like', '%Pago global distribuido automáticamente%')
                                 ->orWhere('observaciones', 'like', '%Recibo asignado automáticamente%');
                    });
                })
                ->orderBy('fecha_emision', 'desc')
                ->get();
            
            \Log::info('Recibos encontrados:', [
                'total' => $recibos->count()
            ]);

            $datosDeuda = [];

            foreach ($apartamentos as $apartamento) {
                foreach ($recibos as $recibo) {
                    // Verificar si existe una asignación (manual, automática o por pago global) para este apartamento y recibo
                    $asignacionExistente = $apartamento->pagos
                        ->where('recibo_gasto_comun_id', $recibo->id)
                        ->filter(function($pago) {
                            return strpos($pago->observaciones, 'Asignación manual') !== false ||
                                   strpos($pago->observaciones, 'Pago global distribuido automáticamente') !== false ||
                                   strpos($pago->observaciones, 'Recibo asignado automáticamente') !== false;
                        })
                        ->first();
                    
                    // Solo procesar si existe una asignación
                    if (!$asignacionExistente) {
                        continue;
                    }
                    
                    // Buscar pagos confirmados de este apartamento para este recibo
                    $pagosConfirmados = $apartamento->pagos
                        ->where('recibo_gasto_comun_id', $recibo->id)
                        ->where('estado', 'confirmado');
                    
                    $montoPagado = $pagosConfirmados->sum('monto_pagado');
                    $saldoActual = $recibo->total_recibo - $montoPagado;
                    
                    // Obtener la fecha del último pago para este recibo
                    $ultimoPago = $pagosConfirmados->sortByDesc('fecha_pago')->first();
                    $fechaPago = $ultimoPago ? $ultimoPago->fecha_pago : null;
                    
                    $datosDeuda[] = [
                        'apartamento_id' => $apartamento->id,
                        'recibo_id' => $recibo->id,
                        'propietario' => $apartamento->propietario,
                        'numero_apartamento' => $apartamento->numero,
                        'numero_recibo' => $recibo->numero_recibo,
                        'periodo' => $recibo->fecha_emision,
                        'total_recibo' => $recibo->total_recibo,
                        'total_pagado' => $montoPagado,
                        'fecha_pago' => $fechaPago,
                        'saldo_actual' => $saldoActual,
                        'tiene_deuda' => $saldoActual > 0,
                        'esta_vencido' => $recibo->estaVencido() && $saldoActual > 0
                    ];
                }
            }

            \Log::info('Datos de deuda construidos:', ['count' => count($datosDeuda)]);
            if (count($datosDeuda) > 0) {
                \Log::info('Primer registro de deuda:', $datosDeuda[0]);
            }
            
            // Aplicar filtros adicionales
            $datosDeudaCollection = collect($datosDeuda);
            
            if ($request->filled('estado_deuda')) {
                if ($request->estado_deuda === 'pendiente') {
                    $datosDeudaCollection = $datosDeudaCollection->where('saldo_actual', '>', 0);
                } elseif ($request->estado_deuda === 'pagado') {
                    $datosDeudaCollection = $datosDeudaCollection->where('saldo_actual', '<=', 0);
                }
            }
            
            if ($request->filled('numero_recibo')) {
                $datosDeudaCollection = $datosDeudaCollection->filter(function ($item) use ($request) {
                    return stripos($item['numero_recibo'], $request->numero_recibo) !== false;
                });
            }

            $datos = $datosDeudaCollection->toArray();
            
            // Debug: Log para verificar los datos
            \Log::info('Datos de deuda para correo:', [
                'total_apartamentos' => $apartamentos->count(),
                'total_recibos_activos' => $recibosActivos->count(),
                'datos_deuda_count' => count($datosDeuda),
                'datos_filtrados_count' => count($datos),
                'filtros' => $request->all()
            ]);
            
            // Capturar filtros aplicados
            $filtros = $request->only(['numero_apartamento', 'nombre_propietario', 'estado_deuda', 'numero_recibo']);
            
            // Determinar el apartamento específico si hay filtro
            $apartamento = null;
            if ($request->filled('numero_apartamento')) {
                $apartamento = Apartamento::where('numero', $request->numero_apartamento)->first();
            }
            
            // Determinar el email de destino
            $emailDestino = null;
            
            if ($apartamento && $apartamento->email) {
                // Si hay un apartamento específico y tiene email, enviar a ese email
                $emailDestino = $apartamento->email;
            } else {
                // Si no hay apartamento específico o no tiene email, enviar al admin
                $emailDestino = 'admin@sc.com'; // Email del administrador
            }
            
            // Generar PDF
            $pdf = PDF::loadView('pdfs.reporte-deudas', compact('datos', 'filtros', 'apartamento'))
                ->setPaper('a4', 'landscape')
                ->setOptions([
                    'defaultFont' => 'Arial',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true
                ]);
            
            // Nombre del archivo PDF
            $nombreArchivo = 'reporte_deudas_' . now()->format('Y-m-d_H-i-s') . '.pdf';
            
            // Enviar el correo con PDF adjunto
            Mail::to($emailDestino)->send(
                (new ReporteDeudas($datos, $filtros, $apartamento))
                    ->attachData($pdf->output(), $nombreArchivo, [
                        'mime' => 'application/pdf'
                    ])
            );
            
            $mensaje = 'Reporte enviado exitosamente a ' . $emailDestino . ' con PDF adjunto';
            
            return redirect()->back()->with('success', $mensaje);
            
        } catch (\Exception $e) {
            \Log::error('Error enviando reporte de deudas: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al enviar el reporte: ' . $e->getMessage());
        }
    }
}
