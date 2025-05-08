<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;


//Models
use Modules\Compras\Models\CatUnidadesMedidas;
//Transformers
use Modules\Compras\Transformers\CatUnidadesMedidaResource;
// Utilities
use Illuminate\Support\Facades\Validator;
class CatUnidadesMedidaController extends Controller

{

    /**
     * Recupera todos los registros de catálogos unidades medidas
     */
    public function index()
    {
        $data = CatUnidadesMedidaResource::collection((CatUnidadesMedidas::active()->get()));
        return $data;
    }


    public function create()
    {
        return view('compras::create');
    }

    /**
     * Guarda un nuevo registro
     */
    public function store(Request $request)
    {
         $valiadcion  = Validator::make($request->all(),[
             'nombre' => 'required|string|max:45',
             'abreviatura' => 'required|string|max:45'
         ]);

        $unidadMedida = CatUnidadesMedidas::create($request->all());
        return response()->json([
                'status' => 'success',
                'message' => 'Se ha guardado correctamente',
                'data' => new CatUnidadesMedidaResource($unidadMedida)
            ]);
    }

    /**
     * Recupera una unidad de medida por id
     */
    public function show($id)
    {
        return CatUnidadesMedidas::where('id', $id)->get();
    }

    public function edit($id)
    {
        return view('compras::edit');
    }

    /**
     * Actualiza un registro especifico
     */
    public function update(Request $request, $id)
    {
        $unidadMedida = CatUnidadesMedidas::find($id);
        
        if(!$unidadMedida){
            return response()->json([
                'status' => 'error',
                'message' => 'El registro que intentas modificar no existe',
                'data' => ''
            ]);
        }

        // $validacion  = Validator::make($request->all(),[
        //     'nombre' => 'required|string|max:45',
        //     'abreviatura' => 'required|string|max:45'
        // ]);

        // if($validacion->fails()){
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Datos inválidos',
        //         'errors' => $validacion->errors()
        //     ]);
        // }
        
        $unidadMedida->update([
            'nombre' => $request->nombre,
            'abreviatura' => $request->abreviatura,
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Se ha actualizado correctamente',
            'data' => ''
        ]);
    }

    /**
     * Actualiza el status a 0 (inactivo) del registro
     */
    public function destroy($id)
    {
        $unidadMedida = CatUnidadesMedidas::where('id', $id);
        
        if(!$unidadMedida){
            return response()->json([
                'status' => 'error',
                'message' => 'El registro que intentas eliminar no existe',
                'data' => ''
            ]);
        }

        $unidadMedida->update([
            'activo' => 0
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Se ha eliminado correctamente',
            'data' => ''
        ]);
    }
}
