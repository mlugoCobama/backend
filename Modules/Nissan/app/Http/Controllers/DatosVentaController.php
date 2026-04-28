<?php

namespace Modules\Nissan\Http\Controllers;

use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Nissan\Models\DatosVenta;
use Modules\Nissan\Models\TipoVenta;
use Modules\Nissan\Models\Vendedor;

class DatosVentaController extends Controller
{
    /**
     *  Volcado masivo de datos de ventas (ejecutado en pruebas)
     */
    public function index()
    {
        $conexiones = ['renault', 'nissan_campestre', 'nissan_azcapotzalco', 'nissan_universidad'];
        // Formato fecha 'AAAA-MM-DD'
        $fechaInicio = '2025-12-01';
        $fechaFin    = '2026-03-18';

        $hoy = new DateTime();
        $ayer = new DateTime();
        $ayer->modify('-1 day');
        
        $fechaInicio = $ayer->format('Y-m-d');
        $fechaFin    = $hoy->format('Y-m-d');


        $resultados = [];

        foreach ($conexiones as $conexion) {
            $resultados[$conexion] = $this->sincronizarVentas($conexion, $fechaInicio, $fechaFin);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Datos sincronizados correctamente',
            'data'    => $resultados
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('nissan::create');
    }

    /**
     * Actualizado masivo de datos de venta a entregado
     */
    public function store(Request $request)
    {
        $data =  $request->all();

        foreach ($data as $item ) {
            $this->updateVenta($item['id']);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Datos entregados correctamente',
            'data' => []
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
     * Devuelve la partida a un estado anterior (dato de venta)
     * @param mixed $request observacion
     * @param mixed $id ide del registro afectado
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $datosVenta = DatosVenta::find($id);
        if($datosVenta){
            $estatusActual = (int) $datosVenta->estatus;
            $datosVenta->estatus = $estatusActual - 1;
            switch ($estatusActual) {
                case 2:
                    $datosVenta->entregado = 0;
                    break;
                case 4:
                    $datosVenta->validado = 0;
                    break;
                case 5:
                    $datosVenta->pagado = 0;
                    break;    
                
                default:
                    break;
            }
            $datosVenta->observacion = $data['observacion'];
            $datosVenta->save();
        }

        return response()->json([
            'status' => 'success',
            'data' => [],
            'message' => 'La partida ha regresado al estado anterior exitosamente'
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Actualizado masivo de datos de venta a validado
     */
    public function storeValidados(Request $request)
    {
        $data =  $request->all();

        foreach ($data as $item ) {
            $this->updateVentaValidados($item['id']);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Datos validados correctamente',
            'data' => []
        ]);
    }

    /**
     * Actualización de partida a entregado
     */
    public function updateVenta($idDatoVenta){
        $partida =  DatosVenta::find($idDatoVenta);
            if($partida && !empty($partida)){
                $partida->entregado =  1;
                $partida->estatus = 2;
                $partida->save();
            }
    }

    /**
     * Actualización de partida a validado
     */
    public function updateVentaValidados($idDatoVenta){
        $partida =  DatosVenta::find($idDatoVenta);
            if($partida && !empty($partida)){
                $partida->validado =  1;
                $partida->estatus = 4;
                $partida->save();
            }
    }

    /**
     * Actualización de partida a pagado
     */
    public function updatePartidaPagado($idDatoVenta){
        $partida =  DatosVenta::find($idDatoVenta);
            if($partida && !empty($partida)){
                $partida->pagado =  1;
                $partida->estatus = 5;
                $partida->save();
            }

        return response()->json([
            'status' => 'success',       
            'message' => "Partida modificada correctamente",
            'data' => [],

        ]);
    }

    /**
     * Actualización de partida a pagado
     */
    public function updatePartidaBDC(Request $request){
    $data =  $request->all();
    foreach ($data as $item ) {
        
        $partida =  DatosVenta::find($item['id']);
            if($partida && !empty($partida)){
                $partida->bdc =  1;
                $partida->save();
            }
    }
        
        return response()->json([
            'status' => 'success',       
            'message' => "Partida modificada correctamente",
            'data' => [],

        ]);
    }

    public function updatePartidaValidacionBDC(Request $request){
    $data =  $request->all();
        foreach ($data as $item ) {
            
            $partida =  DatosVenta::find($item['id']);
                if($partida && !empty($partida)){
                    $partida->v_bdc =  1;
                    $partida->save();
                }
        }
            
            return response()->json([
                'status' => 'success',       
                'message' => "Partida modificada correctamente",
                'data' => [],

            ]);
    }



    private function sincronizarVentas($conexion, $fechaInicio, $fechaFin)
    {
        $libroVentas = DB::connection($conexion)
            ->select("WITH RegistrosOrdenados AS (
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
                        fa.faau_idagencia AS id_agencia,
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
                    ORDER BY fa.faau_fecha, fa.faau_vend_clave", 
                    [$fechaInicio, $fechaFin]
            );

        foreach ($libroVentas as $dato) {
            $existe = DatosVenta::where('no_factura', $dato->faau_nofactura)->exists();
            $agencia = match ($conexion) {
                    'renault'               => $dato->id_agencia,   // usar valor que viene de la base 
                    'nissan_universidad'    => 710,                 // intercompania
                    'nissan_campestre'      => 714,                 // intercompania
                    'nissan_azcapotzalco'  => 730,                 // intercompania
                    default                 => $dato->id_agencia,   // usar valor que viene de la base 
                };

            $vendedorId = null;
            $vendedorExiste = Vendedor::where('nro_vendedor_as', $dato->faau_vend_clave)
                                    ->where('agencia', $agencia)
                                    ->first();

            if (!$vendedorExiste) {
                $vendedor = Vendedor::create([
                    'tipo' => '1',
                    'porcentaje' => '1',
                    'nro_vendedor_as' => $dato->faau_vend_clave,
                    'agencia' => $agencia,
                ]);
                $vendedorId = $vendedor->id;    
            } else {
                $vendedorId = $vendedorExiste->id; 
            }      

            $tipoVenta = TipoVenta::where('nombre', $dato->faau_form_TipoVenta)->first();

            if (!$existe && !empty($vendedorId)) {
                DatosVenta::create([
                    'fecha_as_salida'   => $dato->fecha_salida,
                    'fecha_factura'     => $dato->fecha_factura,
                    'no_factura'        => $dato->faau_nofactura,
                    'razon_social'      => $dato->faau_razonfactura,
                    'descripcion'       => $dato->mode_descripcion,
                    'no_inventario'     => $dato->vehi_numeroinventario,
                    'id_vendedor'       => $vendedorId,
                    'serie'             => $dato->vehi_serie,
                    'total_venta'       => $dato->Venta,
                    'costos'            => $dato->Costo,
                    'bonificaciones'    => $dato->bonificacion,
                    'utilidad_inicial'  => $dato->Utilidad,
                    'tipo_venta_id'     => $tipoVenta->id ?? 2,
                    'tipo_venta'        => $dato->faau_form_TipoVenta,   
                    'clave_producto'    => $dato->vehi_clas_clave,
                    'modelo_producto'   => $dato->mode_clave,
                    'anio_vehiculo'     => $dato->vehi_anio ?? null,
                    'agencia'           => $agencia,
                ]);
            }
        }

        return $libroVentas;
    }

    
}
