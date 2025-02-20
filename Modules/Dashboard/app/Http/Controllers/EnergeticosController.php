<?php

namespace Modules\Dashboard\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\DatosGenerales;

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
    public function store(Request $request)
    {
        $datos = $request->all();

        foreach($datos as $dato){
            if(isset($dato['isNew']) && $dato['isNew']){
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

            }else{
                DB::connection('dashboard1')->table('datos_generales')->
                // DatosGenerales::
                where('sucursales_id', $dato['sucursales_id'] )->
                where('fecha', $dato['fecha'] )->
                update([
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
     * Show the specified resource.
     */
    public function show($mes,$anio)
    {
        $data =  EnergeticosMensualResource::collection( DB::connection('dashboard')->select('call Dashboard.SP_GetDataMesEnergeticos('.$mes.','.$anio.',1)'));

        return response()->json([
            'success' => true,
            'message' => '',
            'data' => $data
        ]);
    }

    public function showGasolinerias($mes,$anio)
    {
        $data =  EnergeticosMensualResource::collection( DB::connection('dashboard')->select('call Dashboard.SP_GetDataMesGasolinerias('.$mes.','.$anio.',2)'));

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
