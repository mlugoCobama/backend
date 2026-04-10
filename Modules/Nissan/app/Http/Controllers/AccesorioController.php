<?php

namespace Modules\Nissan\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Nissan\Models\ComAccesorios;
use Modules\Nissan\Models\ComDetalleAccesorio;
use Modules\Nissan\Models\DatosVenta;
use Modules\Nissan\Transformers\ComAccesoriosResource;

use App\Enums\EstatusComisionesAutos;
use Modules\Nissan\Services\ComisionesService;

class AccesorioController extends Controller
{

    protected $comService;
    public function __construct(ComisionesService $comService)
    {
        $this->comService = $comService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $query = ComAccesorios::active()->with(['vendedor', 'detalles'])->get();
        $query = $this->queryFacturasDetallesAccesorios('2026-01-01', '2026-04-01' );
        // $query = $this->queryFacturasAccesorios('2026-01-01', '2026-04-01' );
        $connectionDestino = DB::connection('autos');
        
        if ($query->isEmpty()) {
            return;
        }

        DB::connection('comisiones')->transaction(function () use ($query, $connectionDestino) {

            foreach ($query as $factura) {
                $facturaId = $connectionDestino->table('com_accesorios')->insertGetId([
                    'no_pedido'     => $factura->pedi_numeropedido,
                    'no_factura'    => $factura->pedi_numerofactura,
                    'fecha_factura'     => $factura->pedi_fechafactura,
                    'empleado_clave'    => $factura->pedi_empl_clave,
                    'empleado_nombre'   => $factura->empl_nombre,
                    'total'             => $factura->pedi_importetotal,
                    'iva'               => $factura->pedi_ivafactura,
                    'razon_social'      => $factura->pedi_razonfactura,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                $detallesInsert = collect($factura->detalles)->map(function ($detalle) use ($facturaId) {
                    return [
                        'factura_id'           => $facturaId,
                        'producto_clave'       => $detalle->depe_prod_claveproducto,
                        'descripcion'          => $detalle->prod_descripcion1,
                        'cantidad'             => $detalle->depe_cantidad,
                        'precio'               => $detalle->depe_precio,
                        'precio_lista'         => $detalle->depe_preciolista,
                        'descuento'            => $detalle->depe_descuento,
                        'fecha_alta'           => $detalle->fechaalta,
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ];
                })->toArray();

                if (!empty($detallesInsert)) {
                    $connectionDestino->table('factura_detalles')->insert($detallesInsert);
                }
            }
        });

        return response()->json(
            [
            'status' =>  'success',
            'data' => $query,
            'registros recuperados' => count($query),
            'message' => 'datos recuperados correctamente',
            ], );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('nissan::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

    $isNoSerie   = strlen($request->factura_vehiculo) === 17;
        $datosVenta  = !$isNoSerie
            ? $this->comService->getVentaByParam('no_factura', $request->factura_vehiculo)
            : $this->comService->getVentaByParam('serie',      $request->factura_vehiculo);


       $comision = $request->filled('id')  ? ComAccesorios::findOrFail($request->id) : new ComAccesorios();

       $comision->com_vendedores_id  = $request->com_vendedores_id;
       $comision->no_pedido  = $request->no_pedido ?? '';
       $comision->iva  = $request->subtotal_factura * 0.16;
       $comision->razon_social  = $request->razon_social;
       $comision->fecha_factura  = $request->fecha;
       $comision->no_factura         = $request->no_factura;
       $comision->sub_total_factura  = $request->subtotal_factura;
       $comision->comision_apv_pesos = $request->subtotal_factura * 0.1;
       
       $comision->agencia         = $request->agencia;
       $comision->observaciones      = $request->observaciones;

       $comision->factura_vehiculo      = $request->factura_vehiculo;
       $comision->com_datos_venta_id      = $datosVenta->id ?? null;

       $comision->save();
       $esNuevo = $comision->wasRecentlyCreated;

    // if ($request->has('detalles')) {
    //     $detalles = json_decode($request->detalles);

    //     $idsExistentes = $comision->detalles()->pluck('id')->toArray();
    //     $idsEnviados   = collect($detalles)->pluck('id')->filter()->toArray();

    //     $aEliminar = array_diff($idsExistentes, $idsEnviados);
    //     ComDetalleAccesorio::destroy($aEliminar);

    //     foreach ($detalles as $detalle) {
    //         if (isset($detalle->id)) {
    //             $this->updateDetalle($detalle->id, $detalle, $comision->id);
    //             // $comision->detalles()->where('id', $detalle['id'])->update($detalle);
    //         } else {
    //             // $comision->detalles()->create($detalle);
    //             $this->createDetalle($detalle, $comision->id);
    //         }
    //     }
    // }

    return response()->json([
        'status'  => 'success',
        'message' => $esNuevo ? 'Comisión creada correctamente' : 'Comisión actualizada correctamente',
        'data'    => []
    ], $esNuevo ? 201 : 200);
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
        $data = $request->all();
        $datosVenta = ComAccesorios::find($id);
        if($datosVenta){
            $estatusActual = (int) $datosVenta->estatus;
            $datosVenta->estatus = $this->comService->devolverEst($estatusActual);
            $datosVenta->comentario = $data['comentario'];
            $datosVenta->save();
        }
        return response()->json([
            'status' => 'success',
            'data' => [],
            'message' => 'La partida ha cambiado al estatus '.$this->comService->getLabelStatus($datosVenta->estatus)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $registro = ComAccesorios::find($id);
        if($registro){
            $registro->activo = 0;
            $registro->save();
           
            return response()->json([
            'status' => 'success',
            'message' => 'Registro eliminado correctamente',
            'data' => []
        ]);
        }
        return response()->json([
            'status' => 'error',
            'message' => 'No se encontró el registro que desea eliminar',
            'data' => []
        ]);
    }

    public function createDetalle($data, $idAccesorio){
        $detalle = new ComDetalleAccesorio();
        $detalle->concepto = $data->concepto;
        $detalle->cantidad = $data->cantidad;
        $detalle->importe = $data->importe;
        $detalle->com_accesorios_id = $idAccesorio;
        $detalle->save();
    }

    public function updateDetalle($id, $data, $idAccesorio){
        $detalle =  ComDetalleAccesorio::find($id);
        if($detalle){
            $detalle->concepto = $data->concepto;
            $detalle->cantidad = $data->cantidad;
            $detalle->importe = $data->importe;
            $detalle->producto_clave = $data->producto_clave ?? '';
            $detalle->precio_lista = $data->precio_lista ?? 0;
            $detalle->descuento = $data->descuento ?? 0;
            $detalle->com_accesorios_id = $idAccesorio;
            $detalle->save();
        }
    }



    public function getDataVenta($noFactura){
        $query = $this->comService->getVentaByParam('no_factura', $noFactura);
        
        return response()->json([
            'status' =>  'success',
            'data' => $query,
            'message' => 'datos recuperados correctamente',
        ], );
    }

    /**
     * Petición de consulta de datos mediante filtro
     * @param mixed $estatus estatus de busqueda (1-5)
     * @param mixed $agencia intercompania de agencia buscada
     * @param mixed $fechaInicio fehcha incial de busqueda
     * @param mixed $fechaFin fecha final de búsqueda
     * @param mixed $vendedor id de vendedor
     */
    public function queryFinanciamientos($estatus, $agencia, $vendedor, $fechaInicio, $fechaFin ){
        $agencia = $this->comService->parseAgencia($agencia);

        if ($fechaInicio) {
        $fechaInicio = Carbon::parse($fechaInicio)->startOfDay()->format('Y-m-d H:i:s');
        }
        if ($fechaFin) {
            $fechaFin = Carbon::parse($fechaFin)->endOfDay()->format('Y-m-d H:i:s');
        }


        $financiamientos = ComAccesorios::
            with(['vendedor', 'detalles', 'venta'])
            ->when($agencia, fn($q) => $q->where('agencia', $agencia))
            ->when($vendedor, fn($q) => $q->where('com_vendedores_id', $vendedor))
            ->when($estatus, fn($q) => $q->where('estatus', $estatus))
            // ->when($tipoVenta, fn($q) => $q->where('clave_producto', $tipoVenta))
            ->when($fechaInicio && $fechaFin , fn($q) => $q->whereBetween('created_at', [$fechaInicio, $fechaFin]))
            ->active()
            ->get();

        return $financiamientos;
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
    public function getTomaUnidad($estatus = null, $agencia =null, $fechaInicio  = null , $fechaFin  = null, $vendedor = null ){
    

    $data = $this->queryFinanciamientos(
                                        $estatus        == '12345' ? null : $estatus,
                                        $agencia        == 'todos' ? null : $agencia,
                                        $vendedor       == 'todos' ? null : $vendedor,
                                        $fechaInicio    == 'todos' ? null : $fechaInicio,
                                        $fechaFin       == 'todos' ? null : $fechaFin
                                    );
    return response()->json([
        'status' => 'success',
        'message' => 'Datos recuperados correctamente',
        'data' => ComAccesoriosResource::collection($data),
        'estado' => $estatus
    ]);

    }

    public function avanzarEstatus($id)
    {
        $datosVenta = ComAccesorios::find($id);
        if($datosVenta){
            $estatusActual = (int) $datosVenta->estatus;
            $datosVenta->estatus = $this->comService->avanzarEst($estatusActual);
            $datosVenta->comentario = null;
            $datosVenta->save();
        }
        return response()->json([
            'status' => 'success',
            'data' => [],
            'message' => 'El estatus cambio a '. $this->comService->getLabelStatus($datosVenta->estatus)
        ]);
    }
}
