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
    public function index()
    {
        $actas = Acta::orderBy('created_at', 'desc')->get();
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
            'nro_acta' => 'required|string|max:255|unique:actas,nro_acta',
            'nombre_acta' => 'required|string|max:20',
            'fecha' => 'required|date',
            'descripcion' => 'required|string|min:10',
            'archivo' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:10240' // 10MB máximo
        ], [
            'nro_acta.required' => 'El número de acta es obligatorio.',
            'nro_acta.unique' => 'Este número de acta ya existe.',
            'nombre_acta.required' => 'El nombre del acta es obligatorio.',
            'fecha.required' => 'La fecha es obligatoria.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.min' => 'La descripción debe tener al menos 10 caracteres.',
            'archivo.mimes' => 'El archivo debe ser de tipo: PDF, DOC, DOCX, JPG o PNG.',
            'archivo.max' => 'El archivo no puede ser mayor a 10MB.'
        ]);

        // Preparar datos para crear el acta
        $datosActa = [
            'nro_acta' => trim($request->nro_acta),
            'nombre_acta' => trim($request->nombre_acta),
            'fecha' => $request->fecha,
            'descripcion' => trim($request->descripcion),
            'archivo_contenido' => null,
            'archivo_nombre' => null,
            'archivo_tipo' => null,
            'archivo_tamaño' => null
        ];

        // Manejar archivo si se subió
        if ($request->hasFile('archivo') && $request->file('archivo')->isValid()) {
            $archivo = $request->file('archivo');
            $nombreArchivo =  'acta_'.($request->nro_acta) . '_' . $archivo->getClientOriginalName();
            
            // Convertir archivo a base64
            $contenidoArchivo = base64_encode(file_get_contents($archivo->getRealPath()));
            
            $datosActa['archivo_contenido'] = $contenidoArchivo;
            $datosActa['archivo_nombre'] = $nombreArchivo;
            $datosActa['archivo_tipo'] = $archivo->getMimeType();
            $datosActa['archivo_tamaño'] = $archivo->getSize();
        }

        // Crear el acta
        Acta::create($datosActa);

        $mensaje = $request->hasFile('archivo') ? 
            'Acta creada exitosamente con archivo adjunto.' : 
            'Acta creada exitosamente sin archivo adjunto.';

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
    public function destroy(string $id)
    {
        //
    }

    /**
     * Download the specified acta file.
     */
    public function download(Acta $acta)
    {
        // Verificar si el acta tiene archivo adjunto
        if (!$acta->archivo_contenido || !$acta->archivo_nombre) {
            abort(404, 'Esta acta no tiene archivo adjunto.');
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
     * Show the import form for actas.
     */
    public function showImport()
    {
        return view('actas.import');
    }

    /**
     * Process the import of actas from CSV file.
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
                if (count($row) >= 4) {
                    Acta::create([
                        'nro_acta' => $row[0],
                        'nombre_acta' => $row[1],
                        'fecha' => $row[2],
                        'descripcion' => $row[3]
                    ]);
                    $imported++;
                }
            } catch (\Exception $e) {
                $errors[] = 'Error en fila ' . ($index + 2) . ': ' . $e->getMessage();
            }
        }
        
        $message = "Se importaron $imported actas exitosamente.";
        if (!empty($errors)) {
            $message .= ' Errores: ' . implode(', ', $errors);
        }
        
        return redirect()->route('actas.index')->with('success', $message);
    }

    /**
     * Download CSV template for actas import.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="plantilla_actas.csv"'
        ];
        
        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['nro_acta', 'nombre_acta', 'fecha', 'descripcion']);
            fputcsv($file, ['ACT001', 'Acta Ejemplo', '2024-01-15', 'Descripción de ejemplo']);
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}