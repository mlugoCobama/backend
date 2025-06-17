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

    /**
     *  Recupera un conjunto de datos basados en una fecha
     * después de validar el periodo existente
     */
    private function conjuntoDatos($mes, $anio, $mesA, $anioA, $anioAnt, $sub_division,  $dataMes){

        $dataMesAnt =  GasolineriaMesResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataMesGasolinerias(' . $mesA . ',' . $anioA . ')'));
        $dataAnioAnt =  GasolineriaMesResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataMesGasolinerias(' . $mes . ',' . $anioAnt . ')'));
        $totalAnio = DataAnualResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataAnualDivision(' . $anio . ',' . $sub_division . ')'));
        $totalAnioAnt = DataAnualResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataAnualDivision(' . $anioAnt . ',' . $sub_division . ')'));
        $totalAnioAnt2 = DataAnualResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataAnualDivision(' . ($anioAnt - 1) . ',' . $sub_division . ')'));
            $data = [
                'mes' => $dataMes,
                'mesAnt' => $dataMesAnt,
                'anioAnt' => $dataAnioAnt,
                'totalAnio' => $totalAnio,
                'totalAnioAnt' => $totalAnioAnt,
                'totalAnioAnt2' => $totalAnioAnt2,
            ];
                
        return $data;
    }

    public function index1(string $sub_division, $mes, $anio){

        // $periodoBuscado = new DateTime("$anio-$mes-01");
        
        /**
         * Cuando el mes es diciembre (12 + 1)
         * Simulamos enero del año siguiente
         */
         $mes = $mes - 1;

        $periodoBuscado = "$anio-$mes-01";
        $date = DateTime::createFromFormat('Y-m-d', $periodoBuscado);
        $fanio = $date->format('Y');
        $fmes = $date->format('m');
        $newPeriodo = "$fanio-$fmes-01";
        do{
            $dataAnio = DB::connection('dashboard')->select('call Dashboard.SP_GetDataAnualDivision(' . $anio . ',' . $sub_division . ')');
            (array)$arrDatos = $dataAnio;
            $arr_mesesDatos = array_map(function($registro) { return $registro->fecha; }, $arrDatos);

            $arr_mesesDatos1 = array_flip($arr_mesesDatos);

            $periodoExiste = isset($arr_mesesDatos1[$newPeriodo]); 
            
            if($periodoExiste === false){
                $anio = $anio - 1;
            }

        }while( count($dataAnio) < 1 );

        if($periodoExiste === false){

            $fecha = end($arr_mesesDatos);

            $data = $this->conjuntoDatos1($fecha, $sub_division,  $dataAnio);

            $nombreMes = $this->meses[intval($mes)];

            return response()->json(
                
                [
                    'success' => true,
                    'message' => "No hay datos de este periodo en su lugar se muestran los de $nombreMes $anio",
                    'data' => $data
                ]

            );
        }else{
            $fecha = $periodoBuscado;
            
            $data = $this->conjuntoDatos1($fecha , $sub_division,  $dataAnio);
            $nombreMes = $this->meses[intval($mes)];
            return response()->json(
            [
                'success' => true,
                'message' => "Mostrando datos de $nombreMes $anio",
                'data' => $data,
            ]
        );
        }
    }

    private function conjuntoDatos1($fechaBusqueda, $sub_division,  $dataAnio){

        $fecha = DateTime::createFromFormat('Y-m-d', $fechaBusqueda);
        $anio =  $fecha->format('Y');
        $mes = $fecha->format('m');
        $anioAnt = $anio - 1;
        $fechaMesA = $fecha->modify('-1 month');
        $mesA = $fechaMesA->format('m');
        $anioA = $fechaMesA->format('Y');

            $dataMes =  GasolineriaMesResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataMesGasolinerias(' . $mes . ',' . $anio . ')'));   
            $dataMesAnt =  GasolineriaMesResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataMesGasolinerias(' . $mesA . ',' . $anioA . ')'));
            $dataAnioAnt =  GasolineriaMesResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataMesGasolinerias(' . $mes . ',' . $anioAnt . ')'));
            $totalAnio =  DataAnualResource::collection($dataAnio);
            $totalAnioAnt = DataAnualResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataAnualDivision(' . $anioAnt . ',' . $sub_division . ')'));
            $totalAnioAnt2 = DataAnualResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataAnualDivision(' . ($anioAnt - 1 ) . ',' . $sub_division . ')'));
                $data = [
                    'mes' => $dataMes,
                    'mesAnt' => $dataMesAnt,
                    'anioAnt' => $dataAnioAnt,
                    'anioAnt2' => $totalAnioAnt2,
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
