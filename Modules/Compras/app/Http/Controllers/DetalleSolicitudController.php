<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Compras\Models\DetalleSolicitud;

class DetalleSolicitudController extends Controller
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
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        
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
        $data = $request->all();

        /**
         * TODO: MDIFICARLO PARA QUE ACEPTE NUEVOS DETALLES
         * PREPARARLO PARA QUE MANEJE EL LOG DE EVENTOS
         */
        foreach ($data as $item) {
            DetalleSolicitud::
            where('id', $item['id'])->update([
                'cantidad' => $item['cantidad'],
                'descripcion' =>  $item['descripcion'],
                'observaciones' =>  $item['observaciones'],
                'cat_unidades_medida_id' =>  $item['unidadMedida']['id'],
                'solicitudes_compra_id' =>  $item['solicitudes_compra_id'],
                'img_referencia' =>  $item['img_referencia'],
                'confirmado' =>  $item['confirmado'],
            ]);
        }

        return response()->json([
            "status" => 'success',
            "message" => 'Actualizado correctamente',
            "data" => []
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
