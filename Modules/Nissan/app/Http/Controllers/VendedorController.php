<?php

namespace Modules\Nissan\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Nissan\Models\Vendedor;
use Modules\Nissan\Transformers\VendedorResource;

class VendedorController extends Controller
{
    /**
     * Recupera los vendedores activos
     */
    public function index()
    {
        $data = Vendedor::active()->get();

        return response()->json([
            'status' => 'success',
            'message' => 'datos recuperados correctamente',
            'data' => VendedorResource::collection($data),
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
     * Almacena el registro de un nuevo vendedor
     */
    public function store(Request $request)
    {
        $data =  $request->all();
        $vendedor = new Vendedor();
        $vendedor->tipo = $data['tipo'];
        $vendedor->nro_vendedor_as = $data['nroAutoSystem'];
        $vendedor->clave = $data['clave'];
        $vendedor->nombre = $data['nombre'];
        $vendedor->agencia = $data['agencia'];
        $vendedor->save();

        return response()->json([
            'status' => 'success', 
            'message' => 'Vendedor agregado correctamente',
            'data' => []
        ]);
    }

    /**
     * Muestra registros de vendedores por agencia
     */
    public function show($id)
    {
        $data = Vendedor::where('agencia',$id)->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Datos recuperados correctamente',
            'data' => VendedorResource::collection($data),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('nissan::edit');
    }

    /**
     * Actualiza el registro del vendedor
     */
    public function update(Request $request, $id)
    {
        $data =  $request->all();
        $vendedor = Vendedor::find($id);
        if($vendedor){
            $vendedor->tipo = $data['tipo'];
            $vendedor->nro_vendedor_as = $data['nroAutoSystem'];
            $vendedor->clave = $data['clave'];
            $vendedor->nombre = $data['nombre'];
            $vendedor->agencia = $data['agencia'];
            $vendedor->save();
        }

        return response()->json([
            'status' => 'success', 
            'message' => 'Vendedor actualizado correctamente',
            'data' => []
        ]);
    }

    /**
     * Marca como inactivo el registro del vendedor
     */
    public function destroy($id)
    {
        $vendedor = Vendedor::find($id);
        if($vendedor){
            $vendedor->activo = 0;
            $vendedor->save();
        }

        return response()->json([
            'status' => 'success', 
            'message' => 'Vendedor borrado correctamente',
            'data' => []
        ]);
    }
}
