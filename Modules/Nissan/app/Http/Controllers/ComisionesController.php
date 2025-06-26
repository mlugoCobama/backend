<?php

namespace Modules\Nissan\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Nissan\Models\Porcentaje;
use Illuminate\Support\Facades\Cache;
use Modules\Nissan\Models\Gasto;
use Modules\Nissan\Transformers\ComisionResource;

class ComisionesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($f_inicio, $f_fin, )
    {
       $registros = DB::connection('nissan_universidad')
                    ->select("
                                WITH RegistrosOrdenados AS (
                                    SELECT *,
                                        ROW_NUMBER() OVER (
                                            PARTITION BY saau_vehi_vehiculoid
                                            ORDER BY fechaalta DESC
                                        ) AS rn
                                    FROM [SISTEMAS].[dbo].[Vt_SalidaAutos]
                                    WHERE CAST(saau_fecha AS DATE) BETWEEN ? AND ?
                                )
                                SELECT 
                                    CAST(fa.faau_fecha AS DATE) AS fecha_factura,
                                    CAST(fa.faau_fechacancelacion AS DATE) AS fecha_cancelacion,
                                    fa.faau_vend_clave,
                                    fa.faau_nofactura, 
                                    fa.faau_razonfactura,
                                    vi.vehi_clas_clave,
                                    vi.vehi_anio,
                                    vi.vehi_numeroinventario,
                                    vi.vehi_serie,
                                    mo.mode_clave,
                                    mo.mode_descripcion,
                                    sa.saau_folio,
                                    CAST(sa.saau_fecha AS DATE) AS fecha_salida,
                                    fa.faau_form_TipoVenta,
                                    sa.saau_vehi_vehiculoid,
                                    fa.faau_iva,
                                    fa.faau_total,
                                    (fa.faau_total - (fa.faau_iva + fa.faau_isan)) as Venta,
                                    (vi.vehi_CostoOperacion + vi.vehi_CostoEquipo + vi.vehi_CostoGastos) AS Costo,
                                    (bo.prim_importe / 1.16) AS bonificacion,
                                    (((fa.faau_total - (fa.faau_iva + fa.faau_isan)) ) - (vi.vehi_CostoOperacion + vi.vehi_CostoEquipo + vi.vehi_CostoGastos )) + (bo.prim_importe / 1.16) as Utilidad
                                FROM [SISTEMAS].[dbo].[Vt_FacturaAutos] fa
                                JOIN RegistrosOrdenados sa
                                    ON fa.faau_vehi_vehiculoid = sa.saau_vehi_vehiculoid
                                JOIN [SISTEMAS].[dbo].[Vt_InventarioAutos] vi
                                    ON fa.faau_vehi_vehiculoid = vi.vehi_vehiculoid
                                JOIN [SISTEMAS].[dbo].[Vt_Modelos] mo
                                    ON mo.mode_modeloid = vi.vehi_mode_modeloid
                                JOIN [SISTEMAS].[dbo].[vt_PrimBonif] bo
                                    ON bo.prim_documento = fa.faau_nofactura
                                WHERE sa.rn = 1
                                AND fa.faau_fechacancelacion IS NULL
                                ORDER BY fa.faau_fecha, fa.faau_vend_clave
                                ", [$f_inicio, $f_fin]);


        return response()->json([
            'status' => 'success',
            'message' => '',
            'data' => ComisionResource::collection($registros),
            // 'data' => $registros
        ]);
    }
    // ", ['2025-06-01','2025-06-10']);
    /**
     * Show the form for creating a new resource.
     */
    public function getPorcentajes(){
    //     $data = Cache::remember('porcentajes_all', now()->addMinutes(10), function () {
    //     return Porcentaje::all();
    // });
        $data = Porcentaje::all();

        return response()->json([
            'status' => 'success',
            'message' => '',
            'data' => $data
        ]);
    }
    public function create()
    {
        return view('nissan::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();

        if($data['isNew']){
            $registro = Gasto::create($request->all());
            
            return response()->json([
            'status' => 'success',
            'message' => 'Datos Guardados correctamente',
            'data' => $registro
        ]);
        }else{
            $registro = Gasto::where('folio_factura', $data['folio_factura'])
            ->update([
            'otros'=>$data['otros'] ?? 0,
            'gasolina'=>$data['gasolina'] ?? 0,
            'previa'=>$data['previa'] ?? 0,
            'descuentos'=>$data['descuentos'] ?? 0,
            'traslados'=>$data['traslados'] ?? 0,
            'descuento_impulso'=>$data['descuento_impulso'] ?? 0,
            'total_subsidios'=>$data['total_subsidios'] ?? 0,
            'descuento_gastos'=>$data['descuento_gastos'] ?? 0,
            'cortesia'=>$data['cortesia'] ?? 0,
            'accesorios'=>$data['accesorios'] ?? 0,
            'placas'=>$data['placas'] ?? 0,
            ]);

            return response()->json([
            'status' => 'success',
            'message' => 'Datos Actualizados correctamente',
            'data' => $data
            ]);

        }
        
        
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('nissan::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('nissan::edit');
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
