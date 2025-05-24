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

    private $meses = array(
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto', 
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        );
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
           
            $intento++;

            if (count($dataMes) > 1 && $intento == 1) {
                $data = $this->conjuntoDatos($mes, $anio, $mesA, $anioA, $anioAnt, $sub_division,  $dataMes);
                $nombreMes = $this->meses[intval($mes)];
                return response()->json([
                    'success' => true,
                    'message' => "Mostrando datos de $nombreMes $anio",
                    'data' => $data,
                    'intento' => $intento,
                ]);
            }

            if (count($dataMes) > 1 && $intento > 1) {
                $data = $this->conjuntoDatos($mes, $anio, $mesA, $anioA, $anioAnt, $sub_division,  $dataMes);
                $nombreMes = $this->meses[intval($mes)];
                return response()->json([
                    'success' => true,
                    'message' => "No hay datos de este periodo en su lugar se muestran los de $nombreMes $anio",
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

    private function conjuntoDatos($mes, $anio, $mesA, $anioA, $anioAnt, $sub_division,  $dataMes){

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
                
        return $data;
    }

    public function create()
    {
        return view('dashboard::create');
    }

    public function store(Request $request): RedirectResponse
    {
        //
    }

    public function show($id)
    {
        return view('dashboard::show');
    }

    public function edit($id)
    {
        return view('dashboard::edit');
    }

    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

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
