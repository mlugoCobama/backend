<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Compras\Models\AcuseEntrega;
use Modules\Compras\Models\AlmacenCompras;

use Modules\Compras\Models\Cotizaciones;
use Modules\Compras\Models\OrdenCompra;
use Modules\Compras\Models\SolicitudesCompra;

use App\Enums\EstatusOrdenCompra;
use App\Enums\EstatusSolicitud;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Modules\Compras\Models\DetalleSolicitud;
use Modules\Ucoip\Services\HardwareService;
use Modules\Ucoip\Services\ResguardosService;

class AcuseEntregaController extends Controller
{

protected $hardwareService;
protected $resguardoService;
    public function __construct(
        HardwareService $hardwareService,
        ResguardosService $resguardoService
    ){
        $this->hardwareService = $hardwareService;
        $this->resguardoService = $resguardoService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {   
        $data = [];

        $solicitudesCompras =  SolicitudesCompra::get();

        foreach ($solicitudesCompras as $solicitud) {
            $detalles = $solicitud->DetallesSolicitud;
            $cotizaciones = $solicitud->Cotizaciones;

            $data["solicitud $solicitud->folio"] = $solicitud;
            $data["solicitud $solicitud->folio"]['detalles'] =  $detalles;
            $data["solicitud $solicitud->folio"]['cotización'] = $cotizaciones;
        }
        
        return response()->json([
            'data' => $data
        ]);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('compras::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store1(Request $request)
    {
        try {
            DB::beginTransaction();

            $userId = $request->user()->id;

            $validated = $request->validate([
                'archivo' => 'required|file|mimes:pdf',
                'observaciones' => 'nullable|string',
                'orden_compra_id' => 'required|integer',
                'detalles_entrada' => 'nullable',
            ]);

            $ordenCompraId = $validated['orden_compra_id'];
            $file = $request->file('archivo');

            $this->storeAcuse($ordenCompraId, $file, $validated);
            $this->ingresarAlmacen($validated['detalles_entrada'], $userId);

            $esEntregaCompleta = $this->esEntregaCompleta($ordenCompraId); 

            $this->actStatusOrdenSolicitud(
                $ordenCompraId,
                EstatusOrdenCompra::ENTREGADA,
                EstatusSolicitud::ENTREGADA,
                $esEntregaCompleta
            );



            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Acuse de entrega creado correctamente',
                'data' => []
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear el acuse de entrega',
                'error' => $e->getMessage()
            ], 500);
        }
    }

public function store(Request $request)
{
    try {

        DB::beginTransaction();

        $userId = $request->user()->id;

        $validated = $request->validate([
            'archivo'           => 'required|file|mimes:pdf',
            'observaciones'     => 'nullable|string',
            'orden_compra_id'   => 'required|integer',
            'usuario_destino'   => 'required|integer',
            'intercompania'     => 'required|integer',
            'proceso'           => 'required'
        ]);

        $proceso = json_decode($validated['proceso'], true);

        $detallesEntrada = $proceso['detalles_entrada'] ?? [];
        $inventario = $proceso['inventario'] ?? [];
        $requiereInventario = $proceso['requiereInventario'] ?? false;

        $ordenCompraId =  $validated['orden_compra_id'];

        $file = $request->file('archivo');


        // guardar acuse
        $this->storeAcuse($ordenCompraId,$file,$validated);
        // ingresar almacén
        $this->ingresarAlmacen(json_encode($detallesEntrada),$userId);
        // guardar inventario solo si aplica
        if($requiereInventario && count($inventario)>0){
            $this->guardarInventario($inventario, $validated['intercompania']);
        }

        $esEntregaCompleta = $this->esEntregaCompleta($ordenCompraId);


        $this->actStatusOrdenSolicitud($ordenCompraId,EstatusOrdenCompra::ENTREGADA,EstatusSolicitud::ENTREGADA,$esEntregaCompleta);
        DB::commit();

        return response()->json([
            'status'=>'success',
            'message'=>'Acuse creado correctamente',
            'data'=> [],
            'esEntragaCompleta' => $esEntregaCompleta
        ]);

    }
    catch(\Throwable $e){

        DB::rollBack();

        return response()->json([
            'status'=>'error',
            'message'=>$e->getMessage()
        ],500);

    }
}



    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $detalles = (DetalleSolicitud::where('solicitudes_compra_id', $id)
        ->with('unidadMedida')
        ->with('almacenCompras.salidas')
                ->confirmadas()
                // ->pendientes()
                ->get());

        $todasEnCero = $detalles->every(function ($detalle) {
            //existencia registrada en la tabla almacen
            $existencias = $detalle->almacenCompras->existencia ?? 0;
            //material que ya fue entregado y que fue usado 
            $salidasDetalle = $detalle->almacenCompras?->salidas ? $detalle->almacenCompras->salidas->sum('cantidad') : 0;
            $materialEntregado = $existencias + $salidasDetalle;

            // $detalle->almacenCompras->existencia = $materialEntregado;

            return ($detalle->cantidad - $materialEntregado) <= 0;
        });
        
                
        return response()->json([
            'status' => 'success',
            'data' => $detalles,
            'todasEnCero' => $todasEnCero,
            'message' => 'Datos obtenidos correctamente'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('compras::edit');
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

    public function esEntregaCompleta(int $ordenCompraId): bool
    {
        $orden = OrdenCompra::with('cotizacion')->find($ordenCompraId);

        if (!$orden || !$orden->cotizacion) {
            return false;
        }

        $solicitudId = $orden->cotizacion->solicitudes_compra_id;

        $detalles = DetalleSolicitud::where('solicitudes_compra_id',$solicitudId)
        ->with('almacenCompras')
        ->confirmadas()
        ->get();

        if ($detalles->isEmpty()) {
            return false;
        }

        return $detalles->every(function ($detalle) {
            $existencia = optional( $detalle->almacenCompras)->existencia ?? 0;
            return $existencia >= $detalle->cantidad;
        });
    }

    public function actStatusOrdenSolicitud(int $idOrdenCompra,int $statusOrdenCompra,int $statusSolicitud,bool $esEntregaCompleta){
    $orden = OrdenCompra::with(['documentos','cotizacion'])->find($idOrdenCompra);

    if(!$orden || !$orden->cotizacion){
        return;
    }

    $solicitud = SolicitudesCompra::find($orden->cotizacion->solicitudes_compra_id);

    if(!$solicitud){
        return;
    }

    $faltanFacturas = $orden->documentos()->count() == 0;

    $estatusOrdenFinal = $statusOrdenCompra;
    $estatusSolicitudFinal = $statusSolicitud;

    // 
    // COMPRAS A CRÉDITO
    // 
    if($orden->modo_pago == 2){
        // Crédito pendiente de pago
        if($orden->pagado != 1){
            if($faltanFacturas){
                $estatusOrdenFinal =EstatusOrdenCompra::FACTURADO;
                $estatusSolicitudFinal =EstatusSolicitud::FACTURADO;
            }else{
                $estatusOrdenFinal =EstatusOrdenCompra::SOLICITADO_PAGO;
                $estatusSolicitudFinal =EstatusSolicitud::SOLICITADO_PAGO;
            }
        }
        else{
            // Ya pagado
            if($esEntregaCompleta){
                $estatusOrdenFinal =EstatusOrdenCompra::FINALIZADA;
                $estatusSolicitudFinal =EstatusSolicitud::FINALIZADA;
            }else{
                $estatusOrdenFinal = $statusOrdenCompra;
                $estatusSolicitudFinal = $statusSolicitud;
            }
        }
    }

    // COMPRAS DE CONTADO
    else{
        if($orden->pagado != 1){
            if($faltanFacturas){
                $estatusOrdenFinal =EstatusOrdenCompra::FACTURADO;
                $estatusSolicitudFinal =EstatusSolicitud::FACTURADO;
            }else{
                $estatusOrdenFinal =EstatusOrdenCompra::SOLICITADO_PAGO;
                $estatusSolicitudFinal =EstatusSolicitud::SOLICITADO_PAGO;
            }
        }
        else{
            if($esEntregaCompleta){
                if($faltanFacturas){
                    $estatusOrdenFinal =EstatusOrdenCompra::FACTURADO;
                    $estatusSolicitudFinal =EstatusSolicitud::FACTURADO;
                }else{
                    $estatusOrdenFinal = EstatusOrdenCompra::FINALIZADA;
                    $estatusSolicitudFinal = EstatusSolicitud::FINALIZADA;
                }
            }else{
                $estatusOrdenFinal = $statusOrdenCompra;
                $estatusSolicitudFinal = $statusSolicitud;
            }
        }
    }

    $orden->estatus = $estatusOrdenFinal;
    $orden->save();

    $solicitud->estatus = $estatusSolicitudFinal;
    $solicitud->save();
}

    /**
     * Recupera un archivo relacionado con una orden de compra desde el servidor.
     *
     * @param int $id ID de la orden de compra.
     * @param string $file Nombre del archivo que se desea recuperar.
     * @return \Illuminate\Http\Response Archivo solicitado como respuesta HTTP con cabecera Content-Type.
     */
    public function getFile($id, $file)
    {
        $path = storage_path("app/acuses/$id/$file");
        if (!File::exists($path)) {
            abort(404);
        }
        $fileContent = File::get($path);
        $type = File::mimeType($path);
        return response($fileContent, 200)->header("Content-Type", $type);
    }

    public function storeAcuse($ordenCompraId, $file, $validated){
        $fechaSubida = now()->format('Y-m-d'); 
        $nombreArchivo = "entrega_orden_{$ordenCompraId}_{$fechaSubida}." . $file->getClientOriginalExtension();

        $carpeta = 'acuses/' . $ordenCompraId;
        $rutaArchivo = $file->storeAs($carpeta, $nombreArchivo);

        $acuse = new AcuseEntrega();
        $acuse->ruta = $rutaArchivo;
        $acuse->comentario = $validated['observaciones'];
        $acuse->fecha = Carbon::now()->format('Y-m-d');
        $acuse->orden_compra_id = $validated['orden_compra_id'];
        $acuse->save();
    }

    public function ingresarAlmacen($dataDetalles,  $idUsuario){
        $datos = json_decode($dataDetalles, true);
        foreach ($datos as  $dato) {
            $itemAlmacen = AlmacenCompras::where('com_detalle_solicitud_id', $dato['id'])->first();
            if(!$itemAlmacen){
                $itemAlmacen = new AlmacenCompras();
            }
            $itemAlmacen->fecha_actualizacion = Carbon::now()->format('Y-m-d');
            $itemAlmacen->existencia = $this->calcularExistencia( $dato['id'], $dato['recibidos'], 1 );
            $itemAlmacen->cantidad = $dato['cantidad'];
            $itemAlmacen->com_detalle_solicitud_id = $dato['id'];
            $itemAlmacen->codigo_producto = null;
            $itemAlmacen->id_usuario = $idUsuario;
            $itemAlmacen->save();
            if($itemAlmacen->existencia == $itemAlmacen->cantidad){
                $this->updateStatusAlmacen($dato['id'], 1);
            } 
        }

    }

    public function calcularExistencia( $idDetalle,  $valorEntrante, $tipo){
        $itemAlmacen = AlmacenCompras::where('com_detalle_solicitud_id', $idDetalle)->first();
        if($itemAlmacen){
            $existencia = $itemAlmacen->existencia + ($valorEntrante * $tipo);
        }else{
            $existencia = ($valorEntrante * $tipo);   
        }
        return $existencia;
    }

    public function updateStatusAlmacen($idDetSol, $status)
    {
        $detalle = DetalleSolicitud::find($idDetSol);
        $detalle->estatus_almacen = $status;
        $detalle->save();
    }

    public function generarEntrada(){

    }

    public function guardarInventario(array $inventario, $intercompania)
    {

        foreach($inventario as $activo){
            $almacen = AlmacenCompras::where('com_detalle_solicitud_id',$activo['detalleId'])->first();

            $hardware = $this->hardwareService
            ->storeHardware([
                'marca'=>$activo['marca'] ?? 'N/D',
                'modelo'=>$activo['modelo'] ?? 'N/D',
                'no_serie'=>$activo['serie'] ?? 'N/D',
                'cat_hardware_id' => $activo['tipo']['id'],
                'mac'=>$activo['mac'] ?? null,
                'memoria_ram'=>$activo['ram'] ?? null,
                'disco_duro'=>$activo['disco'] ?? null,
                'procesador'=>$activo['procesador'] ?? null,
                'caracteristicas'=>$activo['caracteristicas'] ?? '',
                'observaciones'=>$activo['observaciones'] ?? '',
                'estado'=>1,
                'cat_empresa_id'=> 15,
                'almacen_compra_id' => $almacen?->id ?? null
            ]);

            if(!empty($activo['usuario_asignar'])){
              $resguardo = $this->resguardoService->storeResguardo([
                'id_usuario' => $activo['usuario_asignar'],
                'id_empresa' =>  $intercompania,
                'admin_rt' => 2395
               ]);
               $this->resguardoService->storeDetalle($hardware->id, [], $resguardo->id);
                    //  ->asignarEquipo(
                    //     $hardware->id,
                    //   $activo['usuario_asignar']
            // );
            }
        }
    }


}
