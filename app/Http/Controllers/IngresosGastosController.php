<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Resources\IngresosGastosResource;
use App\Models\IngresosGastos;

class IngresosGastosController extends Controller
{
    private $ingresosGastos;

    public function __construct(IngresosGastos $ingresosGastos) {
        $this->ingresosGastos = $ingresosGastos;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $empresa_id, string $mes, string $anio)
    {
        $balanza =  IngresosGastosResource::collection( DB::select('call SP_table_ingresos_gastos('.$mes.','.$anio.','.$empresa_id.')') );

        if (count($balanza) > 0) {
            return response()->json([
                'success' => true,
                'message' => '',
                'data' => $balanza
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No se tiene información captura.',
                'data' => []
            ]);
        }
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
