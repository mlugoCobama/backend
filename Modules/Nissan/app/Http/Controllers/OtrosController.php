<?php

namespace Modules\Nissan\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Nissan\Models\ComCatalogoConceptos;
use Modules\Nissan\Models\ComOtros;

class OtrosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $conceptos = ComCatalogoConceptos::active()->get();
        return response()->json([
            'status'    => 'success',
            'message'   => "Catalogo de conceptos recuperado correctamente",
            'data'      => $conceptos
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
        $comsiones = $request->all();

        $vendedor = $comsiones['vendedor'];
        $agencia = $comsiones['agencia'];
        $conceptos = $comsiones['conceptos'];

        foreach ($conceptos as $concepto) {
            $otroIngreso =  new ComOtros();
            $otroIngreso->com_vendedores_id = $vendedor;
            $otroIngreso->com_catalogo_conceptos_id = $concepto['idConcepto'];
            $otroIngreso->observaciones = $concepto['observaciones'];
            $otroIngreso->importe = $concepto['importe'];
            $otroIngreso->estatus = 3;
            $otroIngreso->agencia = $agencia;
            $otroIngreso->tipo = $concepto['tipo'];
            $otroIngreso->fecha = now();
            $otroIngreso->save();
        }

        return response()->json([
            'data' => [],
            'status' => 'success',
            'message' => 'Datos Guardados Correctamente'
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
        //
    }
}
