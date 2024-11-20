<?php

namespace App\Http\Controllers;

use App\Models\CatEmpresas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


use App\Http\Resources\EmpresasResource;
use App\Http\Resources\ConsultaInfoEnergeticos;

class CatEmpresasController extends Controller
{
    private $empresas;

    public function __construct(CatEmpresas $empresas) {
        $this->empresas = $empresas;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(int $mes, int $anio)
    {
        $data =  ConsultaInfoEnergeticos::collection( DB::connection('dashboard')->select('call Dashboard.SP_GetDataMesEnergeticos('.$mes.','.$anio.',1)') );

        return response()->json([
            'success' => true,
            'message' => '',
            'data' => $data
        ]);
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
    public function show(string $id)
    {
        $data =  EmpresasResource::collection( DB::connection('dashboard')->select('call Dashboard.SP_GetEmpresasSubdivision('.$id.')') );

        return response()->json([
            'success' => true,
            'message' => '',
            'data' => $data
        ]);
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
