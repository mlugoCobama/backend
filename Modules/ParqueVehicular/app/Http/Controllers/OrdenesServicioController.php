<?php

namespace Modules\ParqueVehicular\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\ParqueVehicular\Models\MultimediaProvOrden;
use Modules\ParqueVehicular\Models\OrdenServicio;
use Modules\ParqueVehicular\Models\VehiculoProveedor;

class OrdenesServicioController extends Controller
{
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
    public function store(Request $request): RedirectResponse
    {
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
     * Paso 1 Buscar la orden de servicio activa ligada al proveedor
     * Paso 2 Guardar la nuevas evidencias
     */
    public function update(Request $request, $id)
{
    // Inicializar el arreglo de rutas fuera del try/catch
    $rutasGuardadas = [];

    try {
        DB::transaction(function () use ($request, $id, &$rutasGuardadas) {
                $provOrdenSer = VehiculoProveedor::findOrFail($id);

                // Obtener el arreglo de metadata directamente de la petición
                $fotosMeta = $request->input('fotos_meta', []);

                if ($request->hasFile('nuevas_fotos')) {
                    foreach ($request->file('nuevas_fotos', []) as $i => $file) {
                        if (!$file->isValid()) {
                            continue;
                        }

                        $categoria = $fotosMeta[$i]['categoria'] ?? 'proceso';
                        $comentario = $fotosMeta[$i]['comentario'] ?? null;

                        $extension = $file->guessExtension() ?? $file->getClientOriginalExtension();
                        $mark = time();

                        $fileName = "{$categoria}_{$provOrdenSer->id}_{$i}_{$mark}.{$extension}";
                        $ruta = $file->storeAs("cobama_os/evidencias/{$provOrdenSer->id}/{$categoria}", $fileName, 'public');
                        $rutasGuardadas[] = $ruta;

                        MultimediaProvOrden::create([
                            'pv_orden_proveedor_id' => $provOrdenSer->id,
                            'categoria'             => $categoria,
                            'descripcion'           => $comentario,
                            'ruta'                  => $ruta,
                            'nombre'                => $fileName,
                            'media_type'            => explode('/', $file->getMimeType())[0],
                        ]);
                    }
                }
            });

        } catch (\Throwable $e) {
            if (!empty($rutasGuardadas)) {
                Storage::disk('public')->delete($rutasGuardadas);
            }

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
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }


}
