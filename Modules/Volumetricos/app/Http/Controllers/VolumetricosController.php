<?php

namespace Modules\Volumetricos\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Modules\Volumetricos\Models\ReporteVolumen;
use Modules\Volumetricos\Services\FileService;
use Modules\Volumetricos\Services\ParserJsonService;
use Modules\Volumetricos\Transformers\ReportesVolumetricosResource;

class VolumetricosController extends Controller
{
    protected $fileService;
    protected $jsonService;

    // Inyección de dependencias en el constructor
    public function __construct(
        FileService $fileService,
        ParserJsonService $jsonService,
    ) {
        $this->fileService = $fileService;
         $this->jsonService = $jsonService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data =  ReporteVolumen::active()->latest()->get();

        return response()->json([
            'data' => ReportesVolumetricosResource::collection($data),
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
        'archivo' => 'required|file',
        'tipo' => 'required',
        'descripcion' => 'required',
        'fecha_reporte' => 'required|date',
    ]);

    $archivo = $request->file('archivo');
    $extension = strtolower($archivo->getClientOriginalExtension());

    $rutaOriginal = $this->fileService->almacenarArchivo(
        $archivo,
        $request->empresa,
        $request->tipo,
        $request->fecha_reporte
    );

    $rutaJson = null;

    if (in_array($extension, ['xlsx', 'xls'])) {
        $contenido = $this->jsonService->generateJson($archivo);

        $jsonTexto = is_string($contenido)
            ? $contenido
            : json_encode($contenido, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $rutaJson = $this->fileService->almacenarContenido(
            $jsonTexto,
            $request->empresa,
            $request->tipo,
           $this->parserFecha($request->fecha_reporte) ,
            'json'
        );
    } else if ($extension === 'json' ||  $extension === 'xml' ) {
        $rutaJson = $rutaOriginal;
    }

    $reporte = ReporteVolumen::create([
        'empresa' => $request->empresa,
        'ruta_archivo' => $rutaJson,
        'ruta_plantilla' => $rutaOriginal,
        'tipo' => $request->tipo,
        'uuid_plantilla' => $request->uuid_plantilla ?? null,
        'descripcion' => $request->descripcion,
        'fecha_reporte' => $request->fecha_reporte,
        'comentarios' => $request->comentarios ?? null
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Reporte guardado correctamente',
        'data' => $reporte
    ], 201);
    }

    private function parserFecha($fechaIso){
        $fecha = new DateTime($fechaIso);
        $fechaFormateada = $fecha->format('d-m-Y');

        return $fechaFormateada;
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
        ], 404);
    }

    $disk = Storage::disk('public');

    if (!$disk->exists($reporte->ruta_archivo)) {
        return response()->json([
            'success' => false,
            'message' => 'Archivo no encontrado'
        ], 404);
    }

    $extension = strtolower(
        pathinfo($reporte->ruta_archivo, PATHINFO_EXTENSION)
    );

    $contenido = $disk->get($reporte->ruta_archivo);

    // Eliminar BOM UTF-8
    $contenido = preg_replace(
        '/^\xEF\xBB\xBF/',
        '',
        $contenido
    );

    switch ($extension) {

        case 'json':

            $json = json_decode($contenido, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al interpretar JSON',
                    'error' => json_last_error_msg()
                ], 500);
            }

            return response()->json([
                'success' => true,
                'tipo' => 'json',
                'data' => $json
            ]);

        case 'xml':

            libxml_use_internal_errors(true);

            $xml = simplexml_load_string($contenido);

            if ($xml === false) {

                $errores = [];

                foreach (libxml_get_errors() as $error) {
                    $errores[] = trim($error->message);
                }

                libxml_clear_errors();

                return response()->json([
                    'success' => false,
                    'message' => 'Error al interpretar XML',
                    'errors' => $errores
                ], 500);
            }

            return response()->json([
                'success' => true,
                'tipo' => 'xml',
                'data' => $contenido
            ]);

        default:

            return response()->json([
                'success' => false,
                'message' => 'Tipo de archivo no soportado',
                'extension' => $extension
            ], 415);
    }
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
    public function update(Request $request, $id)
        {
            $reporte = ReporteVolumen::findOrFail($id);

            $request->validate([
                'empresa' => 'required',
                'archivo' => 'nullable|file',
                'tipo' => 'required',
                'descripcion' => 'required',
                'fecha_reporte' => 'required|date',
            ]);

            $rutaOriginal = $reporte->ruta_plantilla;
            $rutaJson = $reporte->ruta_archivo;

            if ($request->hasFile('archivo')) {

                if ($reporte->ruta_plantilla && Storage::disk('public')->exists($reporte->ruta_plantilla)) {
                    Storage::disk('public')->delete($reporte->ruta_plantilla);
                }
                if ($reporte->ruta_archivo && $reporte->ruta_archivo !== $reporte->ruta_plantilla && Storage::disk('public')->exists($reporte->ruta_archivo)) {
                    Storage::disk('public')->delete($reporte->ruta_archivo);
                }

                $archivo = $request->file('archivo');
                $extension = strtolower($archivo->getClientOriginalExtension());

                $rutaOriginal = $this->fileService->almacenarArchivo(
                    $archivo,
                    $request->empresa,
                    $request->tipo,
                    $request->fecha_reporte
                );

                if (in_array($extension, ['xlsx', 'xls'])) {
                    $contenido = $this->jsonService->generateJson($archivo);

                    $jsonTexto = is_string($contenido)
                        ? $contenido
                        : json_encode($contenido, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

                    $rutaJson = $this->fileService->almacenarContenido(
                        $jsonTexto,
                        $request->empresa,
                        $request->tipo,
                        $this->parserFecha($request->fecha_reporte),
                        'json'
                    );
                } else if ($extension === 'json') {
                    $rutaJson = $rutaOriginal;
                }
            }

            $reporte->update([
                'empresa' => $request->empresa,
                'ruta_archivo' => $rutaJson,
                'ruta_plantilla' => $rutaOriginal,
                'tipo' => $request->tipo,
                'descripcion' => $request->descripcion,
                'fecha_reporte' => $request->fecha_reporte,
                'comentarios' => $request->comentarios ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reporte actualizado correctamente',
                'data' => $reporte
            ], 200);
        }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $registro =  ReporteVolumen::find($id);
        if($registro){
            $registro->activo = 0;
            $registro->save();
        }

        return response()->json([
            'data' => [],
            'status' => 'success',
            'message' => 'Registro eliminado correctamente'
        ]);
    }


public function descargarExcel($id)
{
    $reporte = ReporteVolumen::findOrFail($id);

    if (
        !$reporte->ruta_plantilla ||
        !Storage::disk('public')->exists($reporte->ruta_plantilla)
    ) {
        return response()->json([
            'success' => false,
            'message' => 'El archivo Excel no existe en el servidor.'
        ], 404);
    }

    // Extensiones permitidas para archivos Excel
    $extensionesExcel = [
        'xls',
        'xlsx',
        'xlsm',
        'xlt',
        'xltx',
        'xltm',
        'csv'
    ];

    $extension = strtolower(
        pathinfo($reporte->ruta_plantilla, PATHINFO_EXTENSION)
    );

    if (!in_array($extension, $extensionesExcel, true)) {
        return response()->json([
            'success' => false,
            'message' => 'El archivo especificado no es un archivo Excel válido.',
            'extension' => $extension
        ], 415);
    }

    $nombreDescarga = pathinfo(
        $reporte->ruta_plantilla,
        PATHINFO_BASENAME
    );

    $rutaAbsoluta = Storage::disk('public')
        ->path($reporte->ruta_plantilla);

    return response()->download(
        $rutaAbsoluta,
        $nombreDescarga
    );
}

public function descargar($id)
{
    $reporte = ReporteVolumen::findOrFail($id);

    if (!$reporte->ruta_archivo) {
        return response()->json([
            'success' => false,
            'message' => 'El reporte no tiene un archivo asociado.'
        ], 404);
    }

    $disk = Storage::disk('public');

    if (!$disk->exists($reporte->ruta_archivo)) {
        return response()->json([
            'success' => false,
            'message' => 'El archivo no existe en el servidor.'
        ], 404);
    }

    $ruta = $disk->path($reporte->ruta_archivo);

    $nombre = pathinfo(
        $reporte->ruta_archivo,
        PATHINFO_BASENAME
    );

    return response()->download($ruta, $nombre);
}
}
