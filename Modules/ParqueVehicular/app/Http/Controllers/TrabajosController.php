<?php

namespace Modules\ParqueVehicular\Http\Controllers;

use App\Enums\EstatusOrdenesServicio;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\ParqueVehicular\Models\MultimediaProvOrden;
use Modules\ParqueVehicular\Models\OrdenProveedor;
use Modules\ParqueVehicular\Models\OrdenServicio;
use Modules\ParqueVehicular\Models\PartesTrabajo;
use Modules\ParqueVehicular\Models\Trabajos;
use Modules\ParqueVehicular\Services\EventosOsService;
use Modules\ParqueVehicular\Services\OrdenServicioCobService;

class TrabajosController extends Controller
{

    public function __construct(
        protected EventosOsService $eventosService,
        protected OrdenServicioCobService $ordeServicioService) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('parquevehicular::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('parquevehicular::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
        'orden_proveedor_id'               => 'required',
        'descripcion'                      => 'required|string|min:10',
        'trabajos'                         => 'required|array|min:1',
        'trabajos.*.concepto'              => 'required|string',
        'trabajos.*.observaciones'         => 'nullable|string',
        'trabajos.*.partes'                => 'nullable|array',
        'trabajos.*.partes.*.nombre_parte' => 'required|string',
        'trabajos.*.partes.*.numero_parte' => 'nullable|string',
        'trabajos.*.partes.*.cantidad'     => 'required|integer|min:1',
        'fotos'                            => 'nullable|array',
        'fotos.*'                          => 'file', //10 MB
        'fotos_meta'                       => 'nullable|array',
        'fotos_meta.*.categoria'           => 'nullable|string',
        'fotos_meta.*.comentario'          => 'nullable|string',
    ]);

    $rutasGuardadas = [];

    try {
        DB::transaction(function () use ($request, $data, &$rutasGuardadas) {
            //Finalizar el ultimo evento en este caso El diagnostico
            $this->eventosService->finalizarUltimoEvento($data['orden_proveedor_id']);

            $orden = OrdenProveedor::findOrFail( $data['orden_proveedor_id']);
            $orden->diagnostico = $data['descripcion'];
            $orden->save();

            foreach ($data['trabajos'] as $trabajo) {
                $newTrabajo = Trabajos::create([
                    'descripcion'           => $trabajo['concepto'],
                    'pv_orden_proveedor_id' => $data['orden_proveedor_id'],
                ]);

                foreach ($trabajo['partes'] ?? [] as $parte) {
                    PartesTrabajo::create([
                        'descripcion_parte'   => $parte['nombre_parte'],
                        'no_parte'            => $parte['numero_parte'] ?? null,
                        'cantidad'            => $parte['cantidad'],
                        'pv_trabajos_orden_id' => $newTrabajo->id,
                    ]);
                }
            }

            // Archivos
            foreach ($request->file('fotos', []) as $i => $file) {
                if (!$file->isValid()) {
                    continue;
                }

                // Extracción segura de la categoría y comentario
                $categoria = $data['fotos_meta'][$i]['categoria'] ?? 'diagnostico';
                $comentario = $data['fotos_meta'][$i]['comentario'] ?? null;

                // Sanitización de la categoría para evitar caracteres no válidos en rutas

                // Generar extensión segura y nombre único
                $extension = $file->guessExtension() ?? $file->getClientOriginalExtension();
                $mark = time();
                $fileName = "{$categoria}_{$orden->id}_{$i}_{$mark}.{$extension}";

                // Guardar el archivo en el disco público
                $ruta = $file->storeAs("cobama_os/evidencias/{$data['orden_proveedor_id']}/diagnostico", $fileName, 'public');
                $rutasGuardadas[] = $ruta;


                MultimediaProvOrden::create([
                    'pv_orden_proveedor_id' => $orden->id,
                    'categoria'       => $categoria ?? 'diagnostico',
                    'descripcion'     => $comentario ?? null,
                    'ruta'            => $ruta,
                    'nombre'          => $fileName,
                    'media_type'      => explode('/', $file->getMimeType())[0],
                ]);
            }

            $this->ordeServicioService->updateEstatus($orden->pv_orden_servicio_id, EstatusOrdenesServicio::POR_AUTORIZAR);
            $this->eventosService->inciarEvento($data['orden_proveedor_id'], EstatusOrdenesServicio::POR_AUTORIZAR);
        });


    } catch (\Throwable $e) {
        Storage::disk('public')->delete($rutasGuardadas);
        report($e);

        return response()->json([
            'status'  => 'error',
            'message' => 'No se pudo guardar el diagnóstico',
        ], 500);
    }

    return response()->json([
        'data'    => [],
        'status'  => 'success',
        'message' => 'Datos guardados correctamente',
    ]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('parquevehicular::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('parquevehicular::edit');
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
