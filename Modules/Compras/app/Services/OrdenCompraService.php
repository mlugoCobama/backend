<?php

namespace Modules\Compras\Services;

use App\Mail\SolicitudSurtido;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Compras\Http\Controllers\OrdenCompraPdfController;
use Modules\Compras\Models\Cotizaciones;
use Modules\Compras\Models\CotizacionesProveedores;
use Modules\Compras\Models\DatosVehiculo;
use Modules\Compras\Models\DetallesCotizacion;
use Modules\Compras\Models\DetalleSolicitud;
use Modules\Compras\Models\OrdenCompra;
use Modules\Compras\Models\ProveedorContacto;
use Modules\Compras\Models\Proveedores;
use Modules\Compras\Models\SolicitudesCompra;
use Modules\Compras\Transformers\DetallesCotizacionResource;
use Modules\Compras\Transformers\DetalleSolicitudCompraResource;
use Modules\Compras\Transformers\UsersResource;

class OrdenCompraService{

    /**
     * Genera  un folio de orden de compra
     */
    public function generarFolio()
    {
        $ultimaOrden = OrdenCompra::orderBy('id', 'desc')->first();
            if ($ultimaOrden) {
                $ultimoFolio = $ultimaOrden->folio_oc;
                $numero = intval(substr($ultimoFolio, 3)) + 1;
            } else {
                $numero = 1;
            }
        $nuevoFolio = 'OC-' . str_pad($numero, 5, '0', STR_PAD_LEFT);

        return $nuevoFolio;
    }

    /**
     * Recupera una orden de compra en base a un id de cotizacion
     */
    public function consultarOrdenCompraByCotizacion($idCotizacion){
        return OrdenCompra::where('cotizaciones_id', $idCotizacion)->first();
    }

    /**
     * Genera un nuevo registro de orden de compra
     */
    public function storeOrden($data){
        $ordenCompra = new OrdenCompra();
        $ordenCompra->folio_oc = $this->generarFolio();
        $ordenCompra->fecha = now();
        $ordenCompra->entrega = $data["entrega"];
        $ordenCompra->modo_pago = $data["modoPago"];
        $ordenCompra->observaciones = $data["observaciones"];
        $ordenCompra->cotizaciones_id = $data["cotizaciones_id"];
        $ordenCompra->fecha_entrega = $data["fechaEntrega"];
        $ordenCompra->save();
    }


    /**
     * Actualiza una orden de compra en abse a un modelo generado anteriormente
     */
    public function actualizarOrdenCompra($ordenCompra, $data)
    {
        $ordenCompra->observaciones = $data["observaciones"];
        $ordenCompra->entrega = $data["entrega"];
        $ordenCompra->modo_pago = $data["modoPago"];
        $ordenCompra->fecha_entrega = $data["fechaEntrega"];
        $ordenCompra->save();

        return $ordenCompra;
    }

    /**
     * Consulta para mostrar una orden de compra con sus detalles
     */
    public function getDataOrdenCompra( $idSolicitudCompra ){
        $solicitudCompra = SolicitudesCompra::where('id', $idSolicitudCompra)->first();
        if($solicitudCompra->tipo == 2){
            $user = UsersResource::collection(DB::connection('dashboard')->select("call SP_GetDataAutotanque($solicitudCompra->usuario_destino, $solicitudCompra->empresa )"));
        }else{
            $user = UsersResource::collection(DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(' . $solicitudCompra->usuario_destino . ')'));
        }

        $userSolicita = DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(' . $solicitudCompra->usuario_solicita . ')');

        $cotizacion = Cotizaciones::where('solicitudes_compra_id', $idSolicitudCompra)->first();

        $ordenCompra = OrdenCompra::where('cotizaciones_id', $cotizacion->id)->first();

        $cotizacionProveedor = CotizacionesProveedores::where('cotizaciones_id', $cotizacion->id)->Seleccionado()->first();

        $proveedor = Proveedores::where('id', $cotizacionProveedor->proveedores_id)->with(['datosPago'])->first();

        $detalleCotizacion = DetallesCotizacionResource::collection((DetallesCotizacion::with(['detalle_solicitud.unidadMedida' ])->where('cotizaciones_proveedores_proveedores_id', $cotizacionProveedor->id)->get()));

        $data =  [
            'ordenCompra' => $ordenCompra,
            'cotizacion' => $cotizacion,
            'cotizacionProveedor' => $cotizacionProveedor,
            'proveedor' => $proveedor,
            'detallesCotizacion' => $detalleCotizacion,
            'solicitudCompra' => $solicitudCompra,
            'destino' => $user,
            'solicita' => $userSolicita
        ];

        //Llamada a la funcion quue genera el formato
            $pdf = new OrdenCompraPdfController();
            $file = $pdf->OrdenCompraFormatoInterno($data);

            // return $file;
            return [
                'folioOrdenCompra' => $ordenCompra->folio_oc,
                'folioSolicitudCompra' => $solicitudCompra->folio,
                'archivoPDF' => $file
            ];
    }

    /**
     * Envía un correo de solicitud de surtido
     */
    public function enviarCorreoSurtido($idOrdenCompra, $rutaComPago = null){

        $ordenCompra =  OrdenCompra::where('id',  $idOrdenCompra )->first();

        $cotizacion = Cotizaciones::where('id',  $ordenCompra->cotizaciones_id)->first();

        if(!$cotizacion){
            throw new \Exception('No se encontro la cotizacion asociada');
        }

        $proveedorSeleccionado = CotizacionesProveedores::where('cotizaciones_id', $cotizacion->id)
                     ->Seleccionado()->with(['datos_proveedor' => function ($query) {
                        $query->select('id', 'nombre', 'correo');
                }])->first(['id', 'proveedores_id', 'seleccionado', 'contacto_id']);

        if(!$proveedorSeleccionado){
                    throw new \Exception('No hay un proveedor seleccionado para esta cotizacion');
        }

                // Recupero los detalles de la cotizacion para el cuerpo del correo
        $detallesSC = DetalleSolicitudCompraResource::collection((DetalleSolicitud::with('DetalleAutotanque.DatosVehiculo')->confirmadas()->where('solicitudes_compra_id', $cotizacion->solicitudes_compra_id)->get()));
        $solicitudCompra = SolicitudesCompra::where('id',$cotizacion->solicitudes_compra_id )->first();
        $unidadDestino = $solicitudCompra->tipo == 2 ? DatosVehiculo::find($solicitudCompra->usuario_destino) : null;
        
        if($detallesSC->isEmpty()){
            throw new \Exception('No se encontraron detalles');
        }

                // Datos para el correo
        $datos = [
                'ordenCompra'=> $ordenCompra,
                'solicitudCompra' => $solicitudCompra,
                'cotizacion' => $cotizacion,
                'proveedor' => $proveedorSeleccionado,
                'detalles' => $detallesSC,
                'unidadDestino' => $unidadDestino
            ];
        
        $correoProveedor = $datos['proveedor']['datos_proveedor']['correo'];
        if(!empty($proveedorSeleccionado->contacto_id)){
            $contacto = ProveedorContacto::find($proveedorSeleccionado->contacto_id);
            if($contacto){
                $correoProveedor = $contacto->correo;
            }
        }
        

        // Notification::route('mail', $datos['proveedor']['datos_proveedor']['correo'])
        //                     ->notify(new SolicitudSurtido($datos));
        $pdfContenido = $this->getDataOrdenCompra($cotizacion->solicitudes_compra_id);

        Mail::to($correoProveedor)->send(new SolicitudSurtido($datos, $pdfContenido['archivoPDF'], $rutaComPago));
    }

}