<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DeudaController extends Controller
{
    /**
     * Mostrar el resumen de deudas por apartamento
     */
    public function index(Request $request)
    {
        $query = Apartamento::with(['pagos', 'pagos.reciboGastoComun'])
            ->orderBy('numero');

        // Filtro por número de apartamento si se proporciona
        if ($request->filled('numero_apartamento')) {
            $query->where('numero', 'like', '%' . $request->numero_apartamento . '%');
        }

        // Filtro por propietario si se proporciona
        if ($request->filled('nombre_propietario')) {
            $query->where('propietario', 'like', '%' . $request->nombre_propietario . '%');
        }

        $apartamentos = $query->get();

        // Obtener todos los recibos para calcular deudas
        $recibos = ReciboGastoComun::where('estado', 'activo')
            ->orderBy('fecha_emision', 'desc')
            ->get();

        // Preparar datos detallados para la tabla
        $datosDeuda = [];
        
        foreach ($apartamentos as $apartamento) {
            foreach ($recibos as $recibo) {
                // Buscar pagos de este apartamento para este recibo
                $pagosRecibo = $apartamento->pagos
                    ->where('recibo_gasto_comun_id', $recibo->id)
                    ->where('estado', 'confirmado');
                
                $montoPagado = $pagosRecibo->sum('monto_pagado');
                $saldoActual = $recibo->total_recibo - $montoPagado;
                
                // Obtener la fecha del último pago para este recibo
                $ultimoPago = $pagosRecibo->sortByDesc('fecha_pago')->first();
                $fechaPago = $ultimoPago ? $ultimoPago->fecha_pago : null;
                
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
                    'esta_vencido' => $recibo->estaVencido() && $saldoActual > 0
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
        
        // Obtener todos los recibos y calcular estado de pago
        $recibos = ReciboGastoComun::where('estado', 'activo')
            ->orderBy('fecha_emision', 'desc')
            ->get();
        
        $detalleRecibos = [];
        
        foreach ($recibos as $recibo) {
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
     * Obtener resumen estadístico de deudas
     */
    public function estadisticas()
    {
        $totalApartamentos = Apartamento::count();
        $apartamentosConDeuda = 0;
        $deudaTotal = 0;
        
        $apartamentos = Apartamento::with(['pagos'])->get();
        $recibos = ReciboGastoComun::where('estado', 'activo')->get();
        
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
        return view('deudas.import');
    }

    public function import(Request $request)
    {
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
                        'email' => $row[3] ?? ''
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
        return view('deudas.import-completo');
    }

    /**
     * Procesar importación completa desde Excel
     */
    public function importCompleto(Request $request)
    {
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
                DB::transaction(function () {
                    Pago::truncate();
                    ReciboGastoComun::truncate();
                    Apartamento::truncate();
                });
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

            DB::commit();

            $message = "Importación completada: {$importedData['apartamentos']} apartamentos, {$importedData['recibos']} recibos, {$importedData['pagos']} pagos.";
            if (!empty($errors)) {
                $message .= " Se encontraron " . count($errors) . " errores.";
            }

            return redirect()->route('deudas.index')
                ->with('success', $message)
                ->with('import_errors', $errors);

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
                        'email' => $rowData['email'] ?? ''
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $error = "Apartamento fila " . ($rowIndex + 2) . ": " . $e->getMessage();
                $errors[] = $error;
                
                if (!$continueOnError) {
                    break;
                }
            }
        }

        return ['imported' => $imported, 'errors' => $errors];
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

        $requiredColumns = ['apartamento', 'concepto', 'monto'];
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
                $apartamento = Apartamento::where('numero', $rowData['apartamento'])->first();
                
                if (!$apartamento) {
                    if ($validateRelations) {
                        $error = "Recibo fila " . ($rowIndex + 2) . ": Apartamento {$rowData['apartamento']} no encontrado";
                        $errors[] = $error;
                        
                        if (!$continueOnError) {
                            break;
                        }
                        continue;
                    } else {
                        // Crear apartamento automáticamente
                        $apartamento = Apartamento::create([
                            'numero' => $rowData['apartamento'],
                            'propietario' => 'Propietario por definir',
                            'telefono' => '',
                            'email' => ''
                        ]);
                    }
                }
                
                Deuda::create([
                    'apartamento_id' => $apartamento->id,
                    'concepto' => $rowData['concepto'],
                    'monto' => $rowData['monto'] ?? 0,
                    'fecha_vencimiento' => isset($rowData['fecha_vencimiento']) && $rowData['fecha_vencimiento'] 
                        ? Carbon::createFromFormat('Y-m-d', $rowData['fecha_vencimiento']) 
                        : now(),
                    'estado' => 'pendiente'
                ]);
                $imported++;
            } catch (\Exception $e) {
                $error = "Recibo fila " . ($rowIndex + 2) . ": " . $e->getMessage();
                $errors[] = $error;
                
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

        $requiredColumns = ['apartamento', 'monto'];
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
                $apartamento = Apartamento::where('numero', $rowData['apartamento'])->first();
                
                if (!$apartamento) {
                    if ($validateRelations) {
                        $error = "Pago fila " . ($rowIndex + 2) . ": Apartamento {$rowData['apartamento']} no encontrado";
                        $errors[] = $error;
                        
                        if (!$continueOnError) {
                            break;
                        }
                        continue;
                    } else {
                        // Crear apartamento automáticamente
                        $apartamento = Apartamento::create([
                            'numero' => $rowData['apartamento'],
                            'propietario' => 'Propietario por definir',
                            'telefono' => '',
                            'email' => ''
                        ]);
                    }
                }
                
                Pago::create([
                    'apartamento_id' => $apartamento->id,
                    'monto' => $rowData['monto'] ?? 0,
                    'fecha_pago' => isset($rowData['fecha_pago']) && $rowData['fecha_pago'] 
                        ? Carbon::createFromFormat('Y-m-d', $rowData['fecha_pago']) 
                        : now(),
                    'metodo_pago' => $rowData['metodo_pago'] ?? 'efectivo',
                    'referencia' => $rowData['referencia'] ?? ''
                ]);
                $imported++;
            } catch (\Exception $e) {
                $error = "Pago fila " . ($rowIndex + 2) . ": " . $e->getMessage();
                $errors[] = $error;
                
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
        $spreadsheet = new Spreadsheet();
        
        // Hoja de Apartamentos
        $apartamentosSheet = $spreadsheet->getActiveSheet();
        $apartamentosSheet->setTitle('Apartamentos');
        $apartamentosSheet->setCellValue('A1', 'numero');
        $apartamentosSheet->setCellValue('B1', 'propietario');
        $apartamentosSheet->setCellValue('C1', 'telefono');
        $apartamentosSheet->setCellValue('D1', 'email');
        
        // Datos de ejemplo
        $apartamentosSheet->setCellValue('A2', '101');
        $apartamentosSheet->setCellValue('B2', 'Juan Pérez');
        $apartamentosSheet->setCellValue('C2', '555-1234');
        $apartamentosSheet->setCellValue('D2', 'juan@email.com');
        
        // Hoja de Recibos
        $recibosSheet = $spreadsheet->createSheet();
        $recibosSheet->setTitle('Recibos');
        $recibosSheet->setCellValue('A1', 'apartamento');
        $recibosSheet->setCellValue('B1', 'concepto');
        $recibosSheet->setCellValue('C1', 'monto');
        $recibosSheet->setCellValue('D1', 'fecha_vencimiento');
        
        // Datos de ejemplo
        $recibosSheet->setCellValue('A2', '101');
        $recibosSheet->setCellValue('B2', 'Administración');
        $recibosSheet->setCellValue('C2', '150000');
        $recibosSheet->setCellValue('D2', '2024-01-31');
        
        // Hoja de Pagos
        $pagosSheet = $spreadsheet->createSheet();
        $pagosSheet->setTitle('Pagos');
        $pagosSheet->setCellValue('A1', 'apartamento');
        $pagosSheet->setCellValue('B1', 'monto');
        $pagosSheet->setCellValue('C1', 'fecha_pago');
        $pagosSheet->setCellValue('D1', 'metodo_pago');
        $pagosSheet->setCellValue('E1', 'referencia');
        
        // Datos de ejemplo
        $pagosSheet->setCellValue('A2', '101');
        $pagosSheet->setCellValue('B2', '150000');
        $pagosSheet->setCellValue('C2', '2024-01-15');
        $pagosSheet->setCellValue('D2', 'transferencia');
        $pagosSheet->setCellValue('E2', 'TRF001');
        
        $writer = new Xlsx($spreadsheet);
        
        $fileName = 'plantilla_completa.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($temp_file);
        
        return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);
    }
}
