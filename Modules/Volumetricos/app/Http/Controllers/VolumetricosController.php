<?php

namespace Modules\Volumetricos\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Modules\Volumetricos\Models\ReporteVolumen;

class VolumetricosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data =  ReporteVolumen::get();

        return response()->json([
            'data' => $data,
            'message' => 'Datos recuperados correctamente',
            'status' => 'success',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('volumetricos::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
        {
        $request->validate([
            'empresa' => 'required',
            // 'archivo' => 'required|file|mimes:json',
            'tipo' => 'required',
            'descripcion' => 'required',
            'fecha_reporte' => 'required',
        ]);

        // Obtener extensión original
        $extension = $request->file('archivo')->getClientOriginalExtension();

        // Convertir fecha a Carbon
        $fechaReporte = Carbon::parse($request->fecha_reporte);

        // Obtener extensión
        $extension = $request->file('archivo')
            ->getClientOriginalExtension();

        // Limpiar valores
        $empresa = str_replace('-', '', $request->empresa);
        $tipo = str_replace('-', '', $request->tipo);

        // Nombre personalizado SIN guiones
        $nombreArchivo =
            'empresa_' .
            $empresa . '_' .
            $tipo . '_' .
            $fechaReporte->format('Ymd') . '_' .
            now()->format('His') .
            '.' .
            $extension;

        // Guardar
        $ruta = $request->file('archivo')
            ->storeAs(
                'volumenes',
                $nombreArchivo,
                'public'
            );



        // Guardar en BD
        ReporteVolumen::create([
            'empresa' => $request->empresa,
            'ruta_archivo' => $ruta,
            'tipo' => $request->tipo,
            'descripcion' => $request->descripcion
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Guardado correctamente',
            'ruta' => $ruta
        ]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $reporte = ReporteVolumen::find($id);

    if (!$reporte) {
        return response()->json([
            'success' => false,
            'message' => 'Registro no encontrado'
        ],404);
    }

    if (!Storage::disk('public')->exists($reporte->ruta_archivo)) {
        return response()->json([
            'success' => false,
            'message' => 'Archivo no encontrado'
        ],404);
    }

    // Leer archivo
    $contenido = Storage::disk('public')
        ->get($reporte->ruta_archivo);

    // Eliminar BOM UTF-8 si existe
    $contenido = preg_replace(
        '/^\xEF\xBB\xBF/',
        '',
        $contenido
    );

    // Decodificar JSON
    $json = json_decode($contenido, true);

    // Verificar errores
    if (json_last_error() !== JSON_ERROR_NONE) {

        return response()->json([
            'success' => false,
            'message' => 'Error al interpretar JSON',
            'error' => json_last_error_msg(),
            'contenido' => $contenido // solo para depurar
        ],500);
    }

    return response()->json([
        'success' => true,
        'data' => $json
    ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('volumetricos::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
