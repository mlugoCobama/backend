<?php

namespace Modules\Nissan\Http\Controllers;

use App\Exports\ComisionesVentasAutosExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Nissan\Models\Porcentaje;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Nissan\Models\DatosVenta;
use Modules\Nissan\Models\Gasto;
use Modules\Nissan\Models\GastosVenta;
use Modules\Nissan\Transformers\ComisionResource;
use Modules\Nissan\Transformers\DatosVentaResource;

class ComisionesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($f_inicio, $f_fin, )
    {
       $registros = $this->queryDataVentasFromAs( $f_inicio, $f_fin, 'nissan_universidad');
        return response()->json([
            'status' => 'success',
            'message' => '',
            'data' => ComisionResource::collection($registros),
        ]);
    }

    public function create()
    {
        return view('nissan::create');
    }

    /**
     * Almacena los gastos de venta
     */
    public function store(Request $request)
    {
        $data = $request->all();

        foreach ($data as $item) {
            if(isset($item['id_gastos']) && !empty($item['id_gastos'])){
                 $this->updateGastos($item);
            }else{
                $this->saveGastos($item);
            }

            if(isset($item['id_venta']) && !empty($item['id_venta'])){
                $partida =  DatosVenta::find($item['id_venta']);
                $partida->estatus = 3;  
                $partida->save();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Datos Actualizados correctamente',
            'data' => $data
        ]);   

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

    /**
     * Recupera los porcentajes desde la base de datos
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

    /**
     * Petición de consulta de datos mediante filtro
     * @param mixed $estatus estatus de busqueda (1-5)
     * @param mixed $agencia intercompania de agencia buscada
     * @param mixed $tipoVenta tipo de venta (nu-semi)
     * @param mixed $fechaInicio fehcha incial de busqueda
     * @param mixed $fechaFin fecha final de búsqueda
     * @param mixed $vendedor id de vendedor
     */
    public function getDatosVentas($estatus = null, $agencia =null, $tipoVenta = null, $fechaInicio  = null , $fechaFin  = null, $vendedor = null ){

    $data = $this->queryDatosVentas(
                                        $estatus        == '12345' ? null : $estatus,
                                        $agencia        == 'todos' ? null : $agencia,
                                        $vendedor       == 'todos' ? null : $vendedor,
                                        $tipoVenta      == 'todos' ? null : $tipoVenta,
                                        $fechaInicio    == 'todos' ? null : $fechaInicio,
                                        $fechaFin       == 'todos' ? null : $fechaFin
                                    );
        return response()->json([
            'status' => 'success',
            'message' => 'Datos recuperados correctamente',
            'data' => DatosVentaResource::collection($data),
            'estado' => $estatus
        ]);

    }


    /**
     * Petición de consulta de datos mediante filtro
     * @param mixed $estatus estatus de busqueda (1-5)
     * @param mixed $agencia intercompania de agencia buscada
     * @param mixed $tipoVenta tipo de venta (nu-semi)
     * @param mixed $fechaInicio fehcha incial de busqueda
     * @param mixed $fechaFin fecha final de búsqueda
     * @param mixed $vendedor id de vendedor
     */
    public function queryDatosVentas($estatus, $agencia, $vendedor, $tipoVenta, $fechaInicio, $fechaFin ){

         $ventas = DatosVenta::
            with(['vendedor', 'tipoVenta', 'gatosVenta'])
            ->when($agencia, fn($q) => $q->where('agencia', $agencia))
            ->when($vendedor, fn($q) => $q->where('id_vendedor', $vendedor))
            ->when($estatus, fn($q) => $q->where('estatus', $estatus))
            ->when($tipoVenta, fn($q) => $q->where('clave_producto', $tipoVenta))
            ->when($fechaInicio && $fechaFin , fn($q) => $q->whereBetween('fecha_factura', [$fechaInicio, $fechaFin]))
            ->get();

        return $ventas;
    }

    public function formatData($data){
        return $data->map(function ($item) {

        $otros = (float) ($item->gatosVenta->otros ?? 0);
        $gasolina = (float) ($item->gatosVenta->gasolina ?? 0);
        $previa = (float) ($item->gatosVenta->previa ?? 0);
        $descuentos = (float) ($item->gatosVenta->descuentos ?? 0);
        $traslados = (float) ($item->gatosVenta->traslados ?? 0);
        $descuento_impulso = (float) ($item->gatosVenta->descuento_impulso ?? 0);
        $descuento_gastos = (float) ($item->gatosVenta->descuento_da ?? 0);
        $cortesia = (float) ($item->gatosVenta->cortesia ?? 0);
        $accesorios = (float) ($item->gatosVenta->accesorios ?? 0);
        $placas = (float) ($item->gatosVenta->placas ?? 0);
        $porcentaje_bdc = (float) ($item->gatosVenta->porcentaje_bdc ?? 0);

        // Calcular total de gastos
        $total_gastos = $otros + $gasolina + $previa + $descuentos + $traslados
            + $descuento_impulso + $descuento_gastos + $cortesia + $accesorios
            + $placas;

        return [
            "id" => $item->id,
            "fecha_as_salida" => date('d/m/Y', strtotime($item->fecha_as_salida)),
            "no_factura" => $item->no_factura,
            "razon_social" => $item->razon_social,
            "descripcion" => $item->descripcion,
            "serie" => $item->serie,
            "clave_inventario" => $item->clave_producto.'-'.$item->anio_vehiculo.'-'.$item->no_inventario,
            "vendedor_agencia" => $item->vendedor->nro_vendedor_as ?? null,
            "tipo_venta_nombre" => $item->tipoVenta->nombre ?? null,
            "fecha_factura" => date('d/m/Y', strtotime($item->fecha_factura)),
            "venta" => $item->total_venta,
            "costos" => $item->costos ,
            "bonificacion_extra"  => $item->bonificaciones,
            "utlidad" => $item->utilidad_inicial,
            "porcentaje_tipo_venta" => $item->tipoVenta->porcentaje,

            'otros' => $otros,
            'gasolina' => $gasolina,
            'previa' => $previa,
            'descuentos' => $descuentos,
            'traslados' => $traslados,
            'descuento_impulso' => $descuento_impulso,
            'descuento_gastos' => $descuento_gastos,
            'cortesia' => $cortesia,
            'accesorios' => $accesorios,
            'total_gastos' => $total_gastos ?? 0,
            'porcentaje_bdc' => ($porcentaje_bdc ?? 0) / 100,
            'comision_apv_pesos' => (float) ($item->gatosVenta->comision_apv_pesos ?? 0),
            'comision_bdc_pesos' => (float) ($item->gatosVenta->comision_bdc_pesos ?? 0),
        ];
    });



    }

    public function downloadReporte( $estatus = null, $agencia =null, $tipoVenta = null, $fechaInicio  = null , $fechaFin  = null, $vendedor = null )
    {
        $data = $this->queryDatosVentas(
                                        $estatus        == '12345' ? null : $estatus,
                                        $agencia        == 'todos' ? null : $agencia,
                                        $vendedor       == 'todos' ? null : $vendedor,
                                        $tipoVenta      == 'todos' ? null : $tipoVenta,
                                        $fechaInicio    == 'todos' ? null : $fechaInicio,
                                        $fechaFin       == 'todos' ? null : $fechaFin
                                    );

        $hoy = date('d_m_Y');
        $semanaActual = now()->weekOfYear;


        $datosFormateados = $this->formatData($data);

        $filename = 'Comisiones_'.$hoy.'_'.$semanaActual.'_periodo_'.$fechaInicio.'_'.$fechaFin.'.xlsx';
        return Excel::download(
            new ComisionesVentasAutosExport($datosFormateados, $estatus),
            $filename,
            null,
            ['Content-Disposition' => 'attachment; filename="'.$filename.'"']
        );
    }

    /**
     * Actualiza los datos de gastos de una partida
     * @param mixed $gastos datos de gastos de partida
     */
    private function updateGastos($gastos){
        $gasto =  GastosVenta::find($gastos['id_gastos']);
        $gasto->otros  = $gastos['otros'] ?? 0;
        $gasto->gasolina = $gastos['gasolina'] ?? 0;
        $gasto->previa = $gastos['previa'] ?? 0;
        $gasto->descuentos = $gastos['descuentos'] ?? 0;
        $gasto->traslados = $gastos['traslados'] ?? 0;
        $gasto->descuento_impulso = $gastos['descuento_impulso'] ?? 0;
        $gasto->total_subsidios = $gastos['total_subsidios'] ?? 0;
        $gasto->descuento_gastos = $gastos['descuento_da'] ?? 0;
        $gasto->cortesia = $gastos['cortesia'] ?? 0;
        $gasto->accesorios = $gastos['accesorios'] ?? 0;
        $gasto->comision_apv_pesos = $gastos['comision_apv'] ?? 0;
        $gasto->porcentaje_bdc = $gastos['porcentaje_bdc'] ?? 0;
        $gasto->comision_bdc_pesos = $gastos['comision_bdc'] ?? 0;
        $gasto->save();
    }

    /**
     * Guarda los datos de gastos de una partida
     * @param mixed $gastos datos de gastos de partida
     */
    private function saveGastos($gastos){
        $gasto = new GastosVenta();
        $gasto->otros  = $gastos['otros'] ?? 0;
        $gasto->gasolina = $gastos['gasolina'] ?? 0;
        $gasto->previa = $gastos['previa'] ?? 0;
        $gasto->descuentos = $gastos['descuentos'] ?? 0;
        $gasto->traslados = $gastos['traslados'] ?? 0;
        $gasto->descuento_impulso = $gastos['descuento_impulso'] ?? 0;
        $gasto->total_subsidios = $gastos['total_subsidios'] ?? 0;
        $gasto->descuento_gastos = $gastos['descuento_da'] ?? 0;
        $gasto->cortesia = $gastos['cortesia'] ?? 0;
        $gasto->accesorios = $gastos['accesorios'] ?? 0;
        $gasto->comision_apv_pesos = $gastos['comision_apv'] ?? 0;
        $gasto->porcentaje_bdc = $gastos['porcentaje_bdc'] ?? 0;
        $gasto->comision_bdc_pesos = $gastos['comision_bdc'] ?? 0;
        $gasto->id_datos_venta = $gastos['id_venta'] ;
        $gasto->save();
    }


    private function queryDataVentasFromAs($fechaInicial, $fechaFinal, $conexion){

        return DB::connection($conexion)
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
                                ", [$fechaInicial, $fechaFinal]);
    }
    
}
