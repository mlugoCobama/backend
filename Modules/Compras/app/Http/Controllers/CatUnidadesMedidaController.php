<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Modules\Compras\Models\CatUnidadesMedidas;
use Modules\Compras\Transformers\CatUnidadesMedidaResource;

class CatUnidadesMedidaController extends Controller

{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CatUnidadesMedidaResource::collection((CatUnidadesMedidas::active()->get()));
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
    public function store(Request $request)
    {
        $unidadMedida = CatUnidadesMedidas::create($request->all());
        
        return response()->json([
            'status' => 'success',
            'message' => 'Se ha guardado correctamente',
            'data' => new CatUnidadesMedidaResource($unidadMedida)
        ]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return CatUnidadesMedidas::where('id', $id)->get();
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
        CatUnidadesMedidas::where('id', $id)
            ->update(['nombre' => $request->nombre,
                      'abreviatura' => $request->abreviatura
        ]);
        return response()->json([
            'status' => 'success',
            'message' => 'Se ha actualizado correctamente',
            'data' => ''
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        CatUnidadesMedidas::where('id', $id)->update(['activo' => 0]);
        return response()->json([
            'status' => 'success',
            'message' => 'Se ha eliminado correctamente',
            'data' => ''
        ]);
    }
}
