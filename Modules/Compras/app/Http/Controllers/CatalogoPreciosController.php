<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Compras\Http\Requests\StoreCatPreciosRequest;
use Modules\Compras\Models\CatalogoPrecios;

class CatalogoPreciosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('compras::index');
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
    public function store(StoreCatPreciosRequest $request)
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            foreach ($validated['productosArray'] as $item) {
                $productoId = $item['producto_id'];

                foreach ($item['preciosProveedores'] as $cotizacion) {
                    CatalogoPrecios::updateOrCreate(
                        [
                            'com_productos_id' => $productoId,
                            'com_proveedores_id' => $cotizacion['proveedor_id'],
                        ],
                        [
                            'precio' => $cotizacion['precio'],
                        ]
                    );
                }
            }

            DB::commit();

            return response()->json([
                 'status' => 'success',
                'message' => 'Catálogo de precios actualizado correctamente.',
                'data' => []
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al guardar los precios: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('compras::show');
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
        //
    }

    public function obtenerPreciosComparativa(Request $request)
    {
        $proveedoresIds = $request->input('proveedores', []);
        $productosIds = $request->input('productos', []);

        $precios = CatalogoPrecios::whereIn('com_proveedores_id', $proveedoresIds)
            ->whereIn('com_productos_id', $productosIds)
            ->get(['com_productos_id', 'com_proveedores_id', 'precio']);

        return response()->json([
            'success' => true,
            'data' => $precios
        ]);
    }
}
