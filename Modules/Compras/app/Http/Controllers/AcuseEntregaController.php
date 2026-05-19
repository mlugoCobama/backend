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

class AcuseEntregaController extends Controller
{
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
    public function store(Request $request)
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



    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $detalles = (DetalleSolicitud::where('solicitudes_compra_id', $id)->with('unidadMedida')->with('almacenCompras')
                ->confirmadas()
                // ->pendientes()
                ->get());

        $todasEnCero = $detalles->every(function ($detalle) {
            return ($detalle->cantidad - ($detalle->almacenCompras->existencia ?? 0)) === 0;
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
        // Obtener la orden y de ahí la solicitud relacionada
        $orden = OrdenCompra::with('cotizacion')->find($ordenCompraId);

        if (!$orden || !$orden->cotizacion) {
            return false;
        }

        $solicitudId = $orden->cotizacion->solicitudes_compra_id;

        // Traer todos los detalles confirmados con su registro de almacén
        $detalles = DetalleSolicitud::where('solicitudes_compra_id', $solicitudId)
            ->with('almacenCompras')
            ->confirmadas()
            ->get();

        if ($detalles->isEmpty()) {
            return false;
        }

        // La entrega es completa solo si TODOS los detalles tienen
        // su existencia igual o mayor a la cantidad solicitada
        return $detalles->every(function ($detalle) {
            $existencia = $detalle->almacenCompras->existencia ?? 0;
            return $existencia >= $detalle->cantidad;
        });
    }

    public function actStatusOrdenSolicitud($idOrdenCompra, $statusOrdenCompra, $estatusSolicitud, $esEntregaCompleta){

        $orden = OrdenCompra::where('id', $idOrdenCompra)->first();
        if ($orden) {
            $facturas = count($orden->documentos);
            $requiereFactura = ($facturas == 0);
            if( $orden->modo_pago == 2 && $orden->pagado != 1) {
                // Flujo comrpas a credito
                $orden->estatus = $requiereFactura  ? EstatusOrdenCompra::FACTURADO: EstatusOrdenCompra::SOLICITADO_PAGO;

            }else{
                // Flujo compras de contado
                if(!$esEntregaCompleta){
                    $orden->estatus = $statusOrdenCompra;
                }else{
                    $orden->estatus = EstatusOrdenCompra::FINALIZADA;
                }
                
            }      
            $orden->save(); 

            $cotizacion = Cotizaciones::where('id', $orden->cotizaciones_id)->first();

            $solicitud = SolicitudesCompra::find($cotizacion->solicitudes_compra_id);
            if ($solicitud) {
                // Flujo comrpas a credito
                if( $orden->modo_pago == 2 && $orden->pagado != 1) {
                    $solicitud->estatus = $requiereFactura ? EstatusSolicitud::FACTURADO : EstatusSolicitud::SOLICITADO_PAGO;
                }else{
                    // Flujo compras de contado
                    $solicitud->estatus = $estatusSolicitud;
                    if(!$esEntregaCompleta){
                        $solicitud->estatus = $estatusSolicitud;
                    }else{
                        $orden->estatus = EstatusSolicitud::FINALIZADA;
                    }
                }  
                $solicitud->save(); 
            }
        }
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


}
