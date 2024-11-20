<?php

namespace Modules\Dashboard\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Resources\GasolineriaMesResource;
use Modules\Dashboard\Transformers\EnergeticosMensualResource;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GetMonthYearController;

class EnergeticosController extends Controller
{
    private $monthYear;

    public function __construct(GetMonthYearController $monthYear)
    {
        $this->monthYear = $monthYear;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(string $sub_division)
    {
        $mes = $this->monthYear->getMonth();
        $mesAnt = $this->monthYear->getMonthPrev();
        $anio = $this->monthYear->getYear();
        $anioAnt = $this->monthYear->getYearPrev();

        $gasolineriaMes =  GasolineriaMesResource::collection( DB::select('call SP_GetDataMesEnergeticos('.$mes.','.$anio.','.$sub_division.')') );
        $gasolineriaMesAnt =  GasolineriaMesResource::collection( DB::select('call SP_GetDataMesEnergeticos('.$mesAnt.','.$anio.','.$sub_division.')') );
        $gasolineriaAnioAnt =  GasolineriaMesResource::collection( DB::select('call SP_GetDataMesEnergeticos('.$mes.','.$anioAnt.','.$sub_division.')') );

        $data = [
            'mes' => $gasolineriaMes,
            'mesAnt' => $gasolineriaMesAnt,
            'anioAnt' => $gasolineriaAnioAnt
        ];

        if (count($gasolineriaMes) > 0) {
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
    public function show($mes,$anio)
    {
        $data =  EnergeticosMensualResource::collection( DB::connection('dashboard')->select('call Dashboard.SP_GetDataMesEnergeticos('.$mes.','.$anio.',1)') );

        return response()->json([
            'success' => true,
            'message' => '',
            'data' => $data
        ]);
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
