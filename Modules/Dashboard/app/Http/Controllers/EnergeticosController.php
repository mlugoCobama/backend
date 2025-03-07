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
        //$mes = $this->monthYear->getMonth();
        $mes = '03';
        //$mesAnt = $this->monthYear->getMonthPrev();
        $mesAnt = '02';
        //$anio = $this->monthYear->getYear();
        $anio = '2024';
        //$anioAnt = $this->monthYear->getYearPrev();
        $anioAnt = '2023';

        $gasolineriaMes =  GasolineriaMesResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataMesEnergeticos(' . $mes . ',' . $anio . ',' . $sub_division . ',\'all\')'));
        $gasolineriaMesAnt =  GasolineriaMesResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataMesEnergeticos(' . $mesAnt . ',' . $anio . ',' . $sub_division . ',\'all\')'));
        $gasolineriaAnioAnt =  GasolineriaMesResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataMesEnergeticos(' . $mes . ',' . $anioAnt . ',' . $sub_division . ',\'all\')'));
        $totalAnio = DataAnualResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataAnualDivision(' . $anio . ',' . $sub_division . ')'));
        $totalAnioAnt = DataAnualResource::collection(DB::connection('dashboard')->select('call Dashboard.SP_GetDataAnualDivision(' . $anioAnt . ',' . $sub_division . ')'));;

        $data = [
            'mes' => $gasolineriaMes,
            'mesAnt' => $gasolineriaMesAnt,
            'anioAnt' => $gasolineriaAnioAnt,
            'totalAnio' => $totalAnio,
            'totalAnioAnt' => $totalAnioAnt,
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
                        'personal' => $dato['personal'] ?? 0,
                        'eficiencia' => $dato['eficiencia'] ?? 0,
                        'ubo' => $dato['ubo'] ?? 0,
                        'utilidad_bruta' => $dato['utilidad_bruta'] ?? 0,
                        'venta_litros' => $dato['venta_litros'] ?? 0,
                        'ventas' => $dato['ventas'] ??  0,
                        'gasto' => $dato['gasto'] ?? 0,
                        'uno' => $dato['gasto'] ?? 0,
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
                        'personal' => $dato['personal'] ?? 0,
                        'eficiencia' => $dato['eficiencia'] ?? 0,
                        'ubo' => $dato['ubo'] ?? 0,
                        'utilidad_bruta' => $dato['utilidad_bruta'] ?? 0,
                        'venta_litros' => $dato['venta_litros'] ?? 0,
                        'ventas' => $dato['ventas'] ?? 0,
                        'gasto' => $dato['gasto'] ?? 0,
                        'uno' => $dato['uno'] ?? 0,

                    ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Los datos llegaron al server',
            'dataNueva' => $datos

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
