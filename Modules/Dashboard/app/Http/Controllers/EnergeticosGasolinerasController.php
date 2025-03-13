<?php

namespace Modules\Dashboard\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;


use Modules\Dashboard\Transformers\EnergeticosMensualResource;

class EnergeticosGasolinerasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('dashboard::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard::create');
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
        return view('dashboard::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('dashboard::edit');
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

    /**
     * Función que recupera datos por mes de gasolineras
     */
    public function showGasolinerias($mes, $anio)
    {
        $data =  EnergeticosMensualResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataMesGasolinerias(' . $mes . ',' . $anio . ')'));

        return response()->json([
            'success' => true,
            'message' => '',
            'data' => $data
        ]);
    }
}
