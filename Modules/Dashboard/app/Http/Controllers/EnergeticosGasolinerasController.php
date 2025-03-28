<?php

namespace Modules\Dashboard\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use App\Http\Resources\GasolineriaMesResource;
use Modules\Dashboard\Transformers\EnergeticosMensualResource;
use Modules\Dashboard\Transformers\DataAnualResource;

use App\Http\Controllers\GetMonthYearController;
use DateTime;


class EnergeticosGasolinerasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $sub_division, $mes, $anio)
    {
        /**
         * Cuando el mes es diciembre (12 + 1)
         * Simulamos enero del año siguiente
         */
        if ($mes > 12) {
            $anio = $anio + 1;
            $mes = 1;
        }
        /**
         * Numero máximo de peticiones 12
         */
        $maxIntentos = 12;
        $intento = 0;
        /**
         * Mientras data mes sea menor o igual a 1 (siempre devuelve 1 por el total)
         * Busca el mes anterior
         */
        do {
            $fechaMes = new DateTime("$anio-$mes-01");
            $fechaMes->modify('-1 month'); 
            $mes = $fechaMes->format('m');
            $anio = $fechaMes->format('Y'); //Fecha ingresada - 1 mes

            $anioAnt = $anio - 1;


            $fechaMesA = $fechaMes->modify('-1 month');
            $mesA = $fechaMesA->format('m');
            $anioA = $fechaMesA->format('Y'); // Año anterior = año ingresado -1

            $dataMes =  GasolineriaMesResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataMesGasolinerias(' . $mes . ',' . $anio . ')'));
            $dataMesAnt =  GasolineriaMesResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataMesGasolinerias(' . $mesA . ',' . $anioA . ')'));
            $dataAnioAnt =  GasolineriaMesResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataMesGasolinerias(' . $mes . ',' . $anioAnt . ')'));
            $totalAnio = DataAnualResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataAnualDivision(' . $anio . ',' . $sub_division . ')'));
            $totalAnioAnt = DataAnualResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataAnualDivision(' . $anioAnt . ',' . $sub_division . ')'));

            $data = [
                'mes' => $dataMes,
                'mesAnt' => $dataMesAnt,
                'anioAnt' => $dataAnioAnt,
                'totalAnio' => $totalAnio,
                'totalAnioAnt' => $totalAnioAnt,
            ];

            $intento++;

            if (count($dataMes) > 1) {
                return response()->json([
                    'success' => true,
                    'message' => '',
                    'data' => $data
                ]);
            }
        } while (count($dataMes) <= 1 && $intento < $maxIntentos);

        // Si no encontró nada en todos los intentos manda esto
        return response()->json([
            'success' => false,
            'message' => 'No se tiene información captura.',
            'data' => []
        ]);
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
