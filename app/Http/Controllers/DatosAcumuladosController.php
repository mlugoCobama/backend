<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\DatosAcumulados;

class DatosAcumuladosController extends Controller
{
    Private $datosAcumulados;

    public function __construct(DatosAcumulados $datosAcumulados)
    {
        $this->datosAcumulados = $datosAcumulados;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => '',
            'data' => []
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->datosAcumulados
            ->create([
                'anio' => $request->anio,
                'mes' => $request->mes,
                'valor' => $request->valor,
                "cat_empresas_id" => $request->cat_empresas_id,
                "cat_reportes_id" => $request->cat_reportes_id
            ]);

        return response()
            ->json([
                'success' => true,
                'message' => 'Se ha guardado la información correctamente.',
                'data'    => ''
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
