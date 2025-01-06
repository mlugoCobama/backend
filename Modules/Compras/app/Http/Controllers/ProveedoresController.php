<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Support\Facades\Validator;
/**
 * Modelos
 */
use Modules\Compras\Models\proveedores;
/**
 * Resources
 */
use Modules\Compras\Transformers\ProveedoresResource;
/**
 * Request
 */
use Modules\Compras\Http\Requests\ProveedoresRequest;

class ProveedoresController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       return response()->json([
            'success' => true,
            'message' => '',
            'data' =>  ProveedoresResource::collection( proveedores::all() )
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
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'nombre' => 'required',
            'contacto' => 'required',
        ]);

        if ($validator->fails()) {
            dd($validator->getMessageBag()->all());
        }

        /*
        $flight = new proveedores();

        $flight->nombre = $request->nombre;
        $flight->contacto = $request->contacto;
        $flight->telefono = $request->telefono;
        $flight->localidad = $request->estado;
        $flight->condiciones = $request->condiciones;
        $flight->servicios = $request->servicios;

        $flight->save();
        */

        proveedores::create([
            "nombre" => $request->nombre,
            "contacto" => $request->contacto,
            "telefono" => $request->telefono,
            "localidad" => $request->estado,
            "condiciones" => $request->condiciones,
            "servicios" => $request->servicios,
        ]);


    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $proveedores =  ProveedoresResource::collection( proveedores::where('id', $id)->get() );

        return $proveedores;
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
}
