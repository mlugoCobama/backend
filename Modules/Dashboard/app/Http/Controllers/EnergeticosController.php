<?php

namespace Modules\Dashboard\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Models\DatosGenerales;

use App\Http\Resources\GasolineriaMesResource;
use Modules\Dashboard\Transformers\EnergeticosMensualResource;
use Modules\Dashboard\Transformers\DataAnualResource;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GetMonthYearController;
use DateTime;

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
    public function index(string $sub_division, $mes, $anio,  string $titular )
    {
        //$mes = $this->monthYear->getMonth();
        // $mes = '2';
        //$mesAnt = $this->monthYear->getMonthPrev();
        // $mesAnt = '1';
        //$anio = $this->monthYear->getYear();
        // $anio = '2025';
        //$anioAnt = $this->monthYear->getYearPrev();
        $anioAnt = $anio-1;

        $fecha = new DateTime("$anio-$mes-01");
        $fecha->modify('-1 month');

        $mesA = $fecha->format('m');
        $anioA = $fecha->format('Y');

        $dataMes =  GasolineriaMesResource::collection( DB::connection('dashboard')->select('call Dashboard.SP_GetDataMesGaseras('.$mes.','.$anio.','.'\''.$titular.'\''.')') );
        $dataMesAnt =  GasolineriaMesResource::collection( DB::connection('dashboard')->select('call Dashboard.SP_GetDataMesGaseras('.$mesA.','.$anioA.','.'\''.$titular.'\''.')') );
        $dataAnioAnt =  GasolineriaMesResource::collection( DB::connection('dashboard')->select('call Dashboard.SP_GetDataMesGaseras('.$mes.','.$anioAnt.','.'\''.$titular.'\''.')'));
        $totalAnio = DataAnualResource::collection( DB::connection('dashboard')->select('call Dashboard.SP_GetDataAnualDivision('.$anio.','.$sub_division.')') );
        $totalAnioAnt = DataAnualResource::collection( DB::connection('dashboard')->select('call Dashboard.SP_GetDataAnualDivision('.$anioAnt.','.$sub_division.')') );        

        $data = [
            'mes' => $dataMes,
            'mesAnt' => $dataMesAnt,
            'anioAnt' => $dataAnioAnt,
            'totalAnio' => $totalAnio,
            'totalAnioAnt' => $totalAnioAnt,
        ];

        if (count($dataMes) > 0) {
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
     * Almacena los datos de captura gaseras/gasolineras
     * en 'datos_generales'
     */
    public function store(Request $request)
    {
        $datos = $request->all();

        foreach ($datos as $dato) {
            // Verifica si el dato es un nuevo registro
            if (isset($dato['isNew']) && $dato['isNew']) {
                DB::connection('dashboard1')->table('datos_generales')->
                    // // DatosGenerales::create
                    insert([
                        'sucursales_id' => $dato['sucursales_id'],
                        'fecha' => $dato['fecha'],
                        'personal' => $dato['Acumulado Personal'] ?? 0,
                        'eficiencia' => $dato['Eficiencia'] ?? 0,
                        'ubo' => $dato['UBO'] ?? 0,
                        'utilidad_bruta' => $dato['UB'] ?? 0,
                        'venta_litros' => $dato['Venta Litros'] ?? 0,
                        'ventas' => $dato['Ventas'] ??  0,
                        'gasto' => $dato['Gasto'] ?? 0,
                        'uno' => $dato['UNO'] ?? 0,
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
                        'personal' => $dato['Acumulado Personal'] ?? 0,
                        'eficiencia' => $dato['Eficiencia'] ?? 0,
                        'ubo' => $dato['UBO'] ?? 0,
                        'utilidad_bruta' => $dato['UB'] ?? 0,
                        'venta_litros' => $dato['Venta Litros'] ?? 0,
                        'ventas' => $dato['Ventas'] ??  0,
                        'gasto' => $dato['Gasto'] ?? 0,
                        'uno' => $dato['UNO'] ?? 0,

                    ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Datos guardados correctamente',
            'data' => []

        ]);
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
