<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Compras\Models\CatalogoPrecios;
use Modules\Compras\Models\CatSistemasAuto;
use Modules\Compras\Models\Productos;
use Illuminate\Support\Str;
use Modules\Compras\Http\Requests\StoreCatProductoRequest;

class CatalogoProductosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $data = CatSistemasAuto::where('tipo', 1)->with('subCategorias.productosActivos.unidadMedida')->get();
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Catalog recuperado correctamente'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('compras::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCatProductoRequest $request)
    {
       $validated = $request->validated();

        try {
            DB::beginTransaction();
            foreach ($validated['items'] as $itemData) {
                if(!empty($itemData['id'])){
                    Productos::where('id', $itemData['id'])
                    ->update(
                        [
                            'clave_interna'      => $itemData['clave_interna'],
                            'com_subcategoria_id'       => $itemData['subcategoria_id'],
                            'cant_default'       => $itemData['cant_default'],
                            'com_cat_unidad_medida_id'  => $itemData['cat_unidad_medida_id'],
                            'descripcion_planta' => Str::cleanText($itemData['descripcion_planta'] ?? null),
                            'descripcion_proveedor'        => Str::cleanText($itemData['descripcion'] ?? null),
                            'observaciones'      => Str::cleanText($itemData['observaciones'] ?? null),
                        ]
                    );
                }else{
                    Productos::updateOrCreate(
                    ['id' => $itemData['id'] ?? null ],
                    [
                        'clave_interna'      => $itemData['clave_interna'],
                        'com_subcategoria_id'       => $itemData['subcategoria_id'],
                        'cant_default'       => $itemData['cant_default'],
                        'com_cat_unidad_medida_id'  => $itemData['cat_unidad_medida_id'],
                        'descripcion_planta' => Str::cleanText($itemData['descripcion_planta'] ?? null),
                        'descripcion_proveedor'        => Str::cleanText($itemData['descripcion'] ?? null),
                        'observaciones'      => Str::cleanText($itemData['observaciones'] ?? null),
                    ]
                );
                }
                
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => [],
                'message' => 'Lote de artículos guardado correctamente.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'data' => [],
                'message' => 'Error al procesar el lote: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $precios = CatalogoPrecios::where('com_proveedores_id', $id)
        ->get(['com_productos_id', 'precio']);

        $mapaPrecios = $precios->keyBy('com_productos_id');

        return response()->json([
            'estatus' => 'success',
            'data' => $mapaPrecios,
            'message' => 'Datos recuperados correctamente'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('compras::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $producto =  Productos::find($id);

         if(!$producto){
            return response()->json([
                'status' => 'error',
                'message' => 'El registro que intentas eliminar no existe',
                'data' => ''
            ]);
        }

        $producto->update([
            'activo' => 0
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Se ha eliminado correctamente',
            'data' => ''
        ]);

    }

    
}
