<?php

namespace Modules\Nissan\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Nissan\Models\CatGastosPredef;
use Modules\Nissan\Transformers\CatGastosPredefinidosResource;

class TabuladorGastosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $gastos = CatGastosPredefinidosResource::collection(CatGastosPredef::active()->get());

        return response()->json([
            'status' =>'success',
            'data' => $gastos,
            'message' => 'Datos recuperados correctamente'
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('nissan::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(!empty($request->id)){
            $gasto = CatGastosPredef::find($request->id);
        }
        else{
            $gasto = new CatGastosPredef();
        }

        $gasto->clave_gasto = $request->clave_gasto;
        $gasto->importe = $request->importe;
        $gasto->agencia = $request->agencia;
        $gasto->tipo_vehiculo = $request->tipo_vehiculo;

        $gasto->save();

        return response()->json([
            'status' => 'success',
            'message' => ' Datso recuperados correctamente',
            'data' => []
        ]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('nissan::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('nissan::edit');
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
        $gasto = CatGastosPredef::find($id);

        if($gasto){
            $gasto->activo = 0;
            $gasto->save();

            return response()->json([
                'data' => [],
                'message' => 'Dato borrado correctamente',
                'status' => 'success'
            ]);
        }

        return response()->json([
                'data' => [],
                'message' => 'El recurso que intentas borrar no existe',
                'status' => 'error'
            ]);
    }
}
