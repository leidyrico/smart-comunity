<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Acta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ActaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Acta::query();

        // Filtro por número de documento
        if ($request->filled('nro_documento')) {
            $query->where('nro_doc', 'like', '%' . $request->nro_documento . '%');
        }

        // Filtro por tipo de documento
        if ($request->filled('tipo_documento')) {
            $query->where('tipo_documento', $request->tipo_documento);
        }

        // Filtro por fecha desde
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        // Filtro por fecha hasta
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        $actas = $query->orderBy('created_at', 'desc')->get();
        
        return view('actas.index', compact('actas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('actas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validación de campos requeridos y archivo
        $request->validate([
            'nro_doc' => 'required|string|max:255|unique:actas,nro_doc',
            'nombre_doc' => 'required|string|max:20',
            'fecha' => 'required|date',
            'descripcion' => 'required|string|min:10',
            'tipo_documento' => 'required|in:Correspondencia,Comunicado,Actas',
            'archivo' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:10240' // 10MB máximo
        ], [
            'nro_doc.required' => 'El número de documento es obligatorio.',
            'nro_doc.unique' => 'Este número de documento ya existe.',
            'nombre_doc.required' => 'El nombre del documento es obligatorio.',
            'fecha.required' => 'La fecha es obligatoria.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.min' => 'La descripción debe tener al menos 10 caracteres.',
            'tipo_documento.required' => 'El tipo de documento es obligatorio.',
            'tipo_documento.in' => 'El tipo de documento debe ser: Correspondencia, Comunicado o Actas.',
            'archivo.mimes' => 'El archivo debe ser de tipo: PDF, DOC, DOCX, JPG o PNG.',
            'archivo.max' => 'El archivo no puede ser mayor a 10MB.'
        ]);

        // Preparar datos para crear el documento
        $datosActa = [
            'nro_doc' => trim($request->nro_doc),
            'nombre_doc' => trim($request->nombre_doc),
            'fecha' => $request->fecha,
            'descripcion' => trim($request->descripcion),
            'tipo_documento' => $request->tipo_documento,
            'archivo_contenido' => null,
            'archivo_nombre' => null,
            'archivo_tipo' => null,
            'archivo_tamaño' => null
        ];

        // Manejar archivo si se subió
        if ($request->hasFile('archivo') && $request->file('archivo')->isValid()) {
            $archivo = $request->file('archivo');
            $nombreArchivo =  'documento_'.($request->nro_doc) . '_' . $archivo->getClientOriginalName();
            
            // Convertir archivo a base64
            $contenidoArchivo = base64_encode(file_get_contents($archivo->getRealPath()));
            
            $datosActa['archivo_contenido'] = $contenidoArchivo;
            $datosActa['archivo_nombre'] = $nombreArchivo;
            $datosActa['archivo_tipo'] = $archivo->getMimeType();
            $datosActa['archivo_tamaño'] = $archivo->getSize();
        }

        // Crear el documento
        Acta::create($datosActa);

        $mensaje = $request->hasFile('archivo') ? 
            'Documento creado exitosamente con archivo adjunto.' : 
            'Documento creado exitosamente sin archivo adjunto.';

        return redirect()->route('actas.index')->with('success', $mensaje);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Acta $acta)
    {
        // Validar que se proporcione la clave de administrador
        $request->validate([
            'admin_password' => 'required|string'
        ], [
            'admin_password.required' => 'La clave de administrador es obligatoria para eliminar documentos.'
        ]);

        // Verificar la clave de administrador (puedes cambiar esta clave según tus necesidades)
        $adminPassword = 'admin123'; // Clave de administrador predefinida
        
        if ($request->admin_password !== $adminPassword) {
            return redirect()->route('actas.index')
                ->with('error', 'Clave de administrador incorrecta.');
        }

        try {
            // Eliminar el documento
            $nombreDocumento = $acta->nombre_doc;
            $acta->delete();

            return redirect()->route('actas.index')
                ->with('success', "El documento '{$nombreDocumento}' ha sido eliminado exitosamente.");
        } catch (\Exception $e) {
            return redirect()->route('actas.index')
                ->with('error', 'Error al eliminar el documento: ' . $e->getMessage());
        }
    }

    /**
     * Download the specified documento file.
     */
    public function download(Acta $acta)
    {
        // Verificar si el documento tiene archivo adjunto
        if (!$acta->archivo_contenido || !$acta->archivo_nombre) {
            abort(404, 'Este documento no tiene archivo adjunto.');
        }

        // Decodificar el archivo desde base64
        $contenidoArchivo = base64_decode($acta->archivo_contenido);
        
        // Determinar el tipo de contenido
        $tipoContenido = $acta->archivo_tipo ?: 'application/octet-stream';
        
        return response($contenidoArchivo)
            ->header('Content-Type', $tipoContenido)
            ->header('Content-Disposition', 'attachment; filename="' . $acta->archivo_nombre . '"')
            ->header('Content-Length', strlen($contenidoArchivo));
    }

    /**
     * Show the import form for documentos.
     */
    public function showImport()
    {
        return view('actas.import');
    }

    /**
     * Process the import of documentos from CSV file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $content = file($path);
        
        // Detectar el separador (coma o punto y coma)
        $firstLine = $content[0] ?? '';
        $separator = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
        
        // Procesar el CSV con el separador detectado
        $data = array_map(function($line) use ($separator) {
            return str_getcsv($line, $separator);
        }, $content);
        
        // Remove header row
        $header = array_shift($data);
        
        $imported = 0;
        $errors = [];
        $skipped = 0;
        
        foreach ($data as $index => $row) {
            try {
                // Verificar que la fila no esté vacía
                if (empty($row) || (count($row) == 1 && empty(trim($row[0])))) {
                    $skipped++;
                    continue;
                }
                
                if (count($row) >= 5) {
                    // Limpiar y validar los datos
                    $nroActa = trim($row[0]);
                    $nombreActa = trim($row[1]);
                    $fecha = trim($row[2]);
                    $descripcion = trim($row[3]);
                    $tipoDocumento = trim($row[4]) ?: 'Actas';
                    
                    // Verificar que los campos requeridos no estén vacíos
                    if (empty($nroActa) || empty($nombreActa) || empty($fecha) || empty($descripcion)) {
                        $errors[] = 'Fila ' . ($index + 2) . ': Campos requeridos vacíos';
                        continue;
                    }
                    
                    // Validar tipo de documento
                    if (!in_array($tipoDocumento, ['Correspondencia', 'Comunicado', 'Actas'])) {
                        $tipoDocumento = 'Actas';
                    }
                    
                    Acta::create([
                        'nro_doc' => $nroActa,
                        'nombre_doc' => $nombreActa,
                        'fecha' => $fecha,
                        'descripcion' => $descripcion,
                        'tipo_documento' => $tipoDocumento
                    ]);
                    $imported++;
                } else {
                    $errors[] = 'Fila ' . ($index + 2) . ': Faltan columnas (se encontraron ' . count($row) . ', se requieren 5)';
                }
            } catch (\Exception $e) {
                $errors[] = 'Error en fila ' . ($index + 2) . ': ' . $e->getMessage();
            }
        }
        
        $message = "Se importaron $imported documentos exitosamente.";
        if ($skipped > 0) {
            $message .= " Se omitieron $skipped filas vacías.";
        }
        if (!empty($errors)) {
            $message .= ' Errores: ' . implode(', ', $errors);
        }
        
        return redirect()->route('actas.index')->with('success', $message);
    }

    /**
     * Download CSV template for documentos import.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="plantilla_documentos.csv"'
        ];
        
        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['nro_doc', 'nombre_doc', 'fecha', 'descripcion', 'tipo_documento']);
            fputcsv($file, ['DOC001', 'Documento Ejemplo', '2024-01-15', 'Descripción de ejemplo', 'Actas']);
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}