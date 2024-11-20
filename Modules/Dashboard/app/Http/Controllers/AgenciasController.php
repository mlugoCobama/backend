<?php

namespace Modules\Dashboard\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Resources\NissanMesResource;

use App\Http\Controllers\GetMonthYearController;
use App\Http\Controllers\Controller;

class AgenciasController extends Controller
{
    private $monthYear;

    public function __construct(GetMonthYearController $monthYear)
    {
        $this->monthYear = $monthYear;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //$mes = $this->monthYear->getMonth();
        $mes = '05';
        //$mesAnt = $this->monthYear->getMonthPrev();
        $mesAnt = '04';
        //$anio = $this->monthYear->getYear();
        $anio = '2024';
        //$anioAnt = $this->monthYear->getYearPrev();
        $anioAnt = '2023';

        $nissanMes =  NissanMesResource::collection( DB::select('call SP_GetDataMesNissan('.$mes.','.$anio.')') );
        $nissanMesAnt =  NissanMesResource::collection( DB::select('call SP_GetDataMesNissan('.$mesAnt.','.$anio.')') );
        $nissanAnioAnt =  NissanMesResource::collection( DB::select('call SP_GetDataMesNissan('.$mes.','.$anioAnt.')') );

        $data = [
            'mes' => $nissanMes,
            'mesAnt' => $nissanMesAnt,
            'anioAnt' => $nissanAnioAnt
        ];

        if (count($nissanMes) > 0) {
            return response()->json([
                'success' => true,
                'message' => '',
                'data' => $data
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
}
