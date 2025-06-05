<?php

namespace Modules\Dashboard\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Models\DatosGenerales;

use App\Http\Resources\GaseraMesResource;
use Modules\Dashboard\Transformers\EnergeticosMensualResource;
use Modules\Dashboard\Transformers\DataAnualResource;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GetMonthYearController;
use DateTime;
use Modules\Dashboard\Transformers\DataAnualEnergeticoResource;

class EnergeticosController extends Controller
{
    private $monthYear;

    private $meses = array(
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto', 
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        );
    public function __construct(GetMonthYearController $monthYear)
    {
        $this->monthYear = $monthYear;
    }
    /**
     * Display a listing of the resource.
     */
    //$mes = $this->monthYear->getMonth();
    // $mes = '2';
    //$mesAnt = $this->monthYear->getMonthPrev();
    // $mesAnt = '1';
    //$anio = $this->monthYear->getYear();
    // $anio = '2025';
    //$anioAnt = $this->monthYear->getYearPrev();
    public function index(string $sub_division, $mes, $anio, string $titular)
    {   
        /**
         * Cuando el mes es diciembre (12 + 1)
         * Simulamos enero del año siguiente
         */
        if($mes > 12){
            $anio= $anio + 1;
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
            $anio = $fechaMes->format('Y');


            $anioAnt = $anio - 1;


            $fechaMesA = $fechaMes->modify('-1 month');
            $mesA = $fechaMesA->format('m');
            $anioA = $fechaMesA->format('Y');

            $dataMes =  GaseraMesResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataMesGaseras(' . $mes . ',' . $anio . ',' . '\'' . $titular . '\'' . ')'));
            

            $intento++;

            if (count($dataMes) > 1 && $intento == 1) {
                $data = $this->conjuntoDatos($mes, $anio, $mesA, $anioA, $anioAnt, $titular, $sub_division,  $dataMes);
                $nombreMes = $this->meses[intval($mes)];
                return response()->json([
                    'success' => true,
                    'message' => "Mostrando datos de $nombreMes $anio",
                    'data' => $data,
                ]);
            }

            if (count($dataMes) > 1 && $intento > 1) {
                $data = $this->conjuntoDatos($mes, $anio, $mesA, $anioA, $anioAnt, $titular, $sub_division,  $dataMes);
                $nombreMes = $this->meses[intval($mes)];
                return response()->json([
                    'success' => true,
                    'message' => "No hay datos del mes actual, en su lugar se muestran los de $nombreMes $anio",
                    'data' => $data,
                ]);
            }
        } while (count($dataMes) <= 1 && $intento < $maxIntentos); 

        
        return response()->json([
            'success' => false,
            'message' => 'No se tiene información capturada en ningún mes.',
            'data' => []
        ]);
    }

    private function conjuntoDatos($mes, $anio, $mesA, $anioA, $anioAnt, $titular, $sub_division,  $dataMes){

        $dataMesAnt =  GaseraMesResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataMesGaseras(' . $mesA . ',' . $anioA . ',' . '\'' . $titular . '\'' . ')'));
        $dataAnioAnt =  GaseraMesResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataMesGaseras(' . $mes . ',' . $anioAnt . ',' . '\'' . $titular . '\'' . ')'));
        $totalAnio = DataAnualResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataAnualDivision(' . $anio . ',' . $sub_division . ')'));
        $totalAnioAnt = DataAnualResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataAnualDivision(' . $anioAnt . ',' . $sub_division . ')'));
        $totalAnioAnt2 = DataAnualResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataAnualDivision(' . ($anioAnt-1) . ',' . $sub_division . ')'));
            $data = [
                'mes' => $dataMes,
                'mesAnt' => $dataMesAnt,
                'anioAnt' => $dataAnioAnt,
                'totalAnio' => $totalAnio,
                'totalAnioAnt' => $totalAnioAnt,
                'totalAnioAnt2' =>  $totalAnioAnt2
            ];

        return $data;
        
    }

    public function getAnualEstacionEnergeticos($idEstacion, $anio){
        
        $anioAnt = $anio - 1;

        $totalAnioEstacion = DataAnualEnergeticoResource::collection(DB::connection('dashboard')->select("call Dashboard.SP_GetDataAnualEnergeticos($idEstacion ,$anio)"));
        
        $totalAnioAntEstacion = DataAnualEnergeticoResource::collection(DB::connection('dashboard')->select("call Dashboard.SP_GetDataAnualEnergeticos($idEstacion ,$anioAnt)"));

        $anioAnt2 = $anio - 2;

        $totalAnioAntEstacion2 = DataAnualEnergeticoResource::collection(DB::connection('dashboard')->select("call Dashboard.SP_GetDataAnualEnergeticos($idEstacion ,$anioAnt2)"));
        
        $data = [
            'totalAnio' => $totalAnioEstacion,
            'totalAnioAnt' => $totalAnioAntEstacion,
            'totalAnioAnt2' => $totalAnioAntEstacion2,
            ];

        return response()->json([
            'success' => true,
            'message' => 'Datos anuales recuperados correctamente',
            'data' => $data
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
     * Almacena los datos de captura gaseras/gasolineras
     * en 'datos_generales'
     */
    public function store(Request $request)
    {
        $datos = $request->all(); 
            $datos = $request->all();

            DB::connection('dashboard1')->beginTransaction();
            try {
                foreach ($datos as $dato) {
                    // Verifica si el dato es un nuevo registro
                    if (isset($dato['isNew']) && $dato['isNew']) {
                        DB::connection('dashboard1')->table('datos_generales')->
                            // // DatosGenerales::create
                            insert([
                                'sucursales_id' => $dato['sucursales_id'],
                                'fecha' => $dato['fecha'],
                                'personal' => str_replace(",", "", $dato['Acumulado Personal'] ?? 0),
                                'eficiencia' => str_replace(",", "", $dato['Eficiencia'] ?? 0),
                                'ubo' => str_replace(",", "", $dato['UBO'] ?? 0),
                                'utilidad_bruta' => str_replace(",", "", $dato['UB'] ?? 0),
                                'venta_litros' => str_replace(",", "", $dato['Venta Litros'] ?? 0),
                                'ventas' => str_replace(",", "", $dato['Ventas'] ??  0),
                                'gasto' => str_replace(",", "", $dato['Gasto'] ?? 0),
                                'uno' => str_replace(",", "", $dato['UNO'] ?? 0),
                            ]);
                        /**
                         * Si no contiene "isNew" actualiza el registro 
                         * Usando como clave el id de sucursal y la fecha del periodo
                         */
                    } else {
                        DB::connection('dashboard1')->table('datos_generales')->
                            // DatosGenerales::
                            where('sucursales_id', $dato['sucursales_id'])->where('fecha', $dato['fecha'])->update([
                                'sucursales_id' => $dato['sucursales_id'],
                                'fecha' => $dato['fecha'],
                                'personal' => str_replace(",", "", $dato['Acumulado Personal'] ?? 0) ,
                                'eficiencia' => str_replace(",", "", $dato['Eficiencia'] ?? 0),
                                'ubo' => str_replace(",", "", $dato['UBO'] ?? 0),
                                'utilidad_bruta' => str_replace(",", "", $dato['UB'] ?? 0),
                                'venta_litros' => str_replace(",", "", $dato['Venta Litros'] ?? 0),
                                'ventas' => str_replace(",", "", $dato['Ventas'] ??  0),
                                'gasto' => str_replace(",", "", $dato['Gasto']  ?? 0),
                                'uno' => str_replace(",", "", $dato['UNO']  ?? 0),

                            ]);
                    }
                }
                DB::connection('dashboard1')->commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Datos guardados correctamente',
                    'data' => []

                ]);
            } catch (\Exception $e) {
                DB::connection('dashboard1')->rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ocurrió un error al guardar los datos',
                    'error' => $e->getMessage()

                ]);
            }       
    }

    /**
     * Función que recupera datos por mes de gaseras
     */
    public function show($mes, $anio)
    {
        $data =  EnergeticosMensualResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataMesGaseras(' . $mes . ',' . $anio . ',\'all\')'));

        return response()->json([
            'success' => true,
            'message' => '',
            'data' => $data
        ]);
    }


    public function showAnual($sub_division, $anio)
    {
        $data =  DataAnualResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataAnualDivision(' . $anio . ',' . $sub_division . ')'));
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
    public function update(Request $request, $id)
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
