<?php

namespace Modules\Compras\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\EstatusOrdenCompra;
use App\Enums\EstatusSolicitud;
use App\Helpers\NotificationHelper;
//Models
use Modules\Compras\Models\OrdenCompra;
use Modules\Compras\Models\DocumentosOrdenesCompra;
use Modules\Compras\Models\SolicitudesCompra;
use Modules\Compras\Models\CotizacionesProveedores;
use Modules\Compras\Models\DetallesCotizacion;
use Modules\Compras\Models\DetalleSolicitud;
use Modules\Compras\Models\Proveedores;
use Modules\Compras\Models\Cotizaciones;
//Transformers
use Modules\Compras\Transformers\DetallesCotizacionResource;
use Modules\Compras\Transformers\DetalleSolicitudCompraResource;
//Utilities
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Notification;
//Mailables
// use App\Notifications\SolicitudSurtido;
use App\Mail\SolicitudSurtido;
use Illuminate\Support\Facades\Mail;
use Modules\Compras\Models\DatosVehiculo;
use Modules\Compras\Models\ProveedorContacto;
use Modules\Compras\Services\CambioEstatusService;
use Modules\Compras\Services\CotizacionesService;
use Modules\Compras\Services\OrdenCompraService;
use Modules\Compras\Transformers\OrdenCompraResource;
use Modules\Compras\Transformers\UsersResource;

class OrdenesComprasController extends Controller
{

    protected $ordenCompraService;
    protected $cotizacionesService;
    protected $statusService;

    // Inyección de dependencias en el constructor
    public function __construct(
        OrdenCompraService $ordenCompraService,
        CotizacionesService $cotizacionesService,
        CambioEstatusService $statusService
    ) {
        $this->ordenCompraService = $ordenCompraService;
        $this->cotizacionesService = $cotizacionesService;
        $this->statusService = $statusService;
    }
    /** **********************************************************
     * Genera la orden de compra en la BD
     ************************************************************/
    public function store(Request $request)
    {
        
        try {
            DB::beginTransaction();
                $data =  $request->all();
                // Validar que la orden de compra no exista
                $ocExistente = $this->ordenCompraService->consultarOrdenCompraByCotizacion($data["cotizaciones_id"]);
                // Si existe solo se modifica
                if($ocExistente){
                    $cotizacion = Cotizaciones::where('solicitudes_compra_id', $data["id_solicitud_compra"])->first();
                        if ($cotizacion) {
                            // Quitar el proveedor seleccionado
                            $this->cotizacionesService->desmarcarCotizacionSeleccionada($data["cotizaciones_id"]);
                            //Asignar le nuevo proveedor seleccionado
                            $this->cotizacionesService->cotizacionProveedorSeleccionada($data['id_cotizacion_prov']);
                            $this->ordenCompraService->actualizarOrdenCompra($ocExistente, $data);
                        }
                }else{
                    $this->ordenCompraService->storeOrden($data);
                    $this->cotizacionesService->cotizacionProveedorSeleccionada($data['id_cotizacion_prov']);
                }
            
                $this->statusService->actStatusSolicitudToOrden($data['id_solicitud_compra'], $data);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Se ha actualizado correctamente',
                'data' => []
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error inesperado',
                'error' => $e->getMessage()
            ]);
        }
    }

    /** **********************************************************
     * Recupera la orden de compra en base al id de cotización
     ************************************************************/
    public function show($id)
    {
        $cotizacion = Cotizaciones::where('solicitudes_compra_id', $id)->first();
        $ordenCompra = new OrdenCompraResource(OrdenCompra::with('cotizacion.SolicitudCompra.SistemaMantenimiento')->where('cotizaciones_id', $cotizacion->id)->first());

        return response()->json([
            'data' => $ordenCompra
        ]);

    }

    public function edit($id)
    {
        return view('compras::edit');
    }

    /** ************************************************************
     * Actualiza el estatus a pagado
     ************************************************************/
    public function update(Request $request, $id)
    {
        $idSc = $request->all();


        try {
            DB::beginTransaction();
                // Actualiza el estatus de OrdenCompra a 5 (Pagado)
                    $orden = OrdenCompra::find($id);
                    if ($orden) {
                        $orden->estatus = EstatusOrdenCompra::PAGADA;
                        $orden->save();
                    }

                    // Actualiza el estatus de SolicitudCompra a 7 (Pagado)
                    $solicitud = SolicitudesCompra::find($idSc);
                    if ($solicitud) {
                        $solicitud->estatus = EstatusSolicitud::PAGADA;
                        $solicitud->save();
                    }
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Se ha realizado correctamente',
                'data' => $idSc
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error inesperado',
                'error' => $e->getMessage()
            ]);
        }

    }

    /** ******************************************************************
     * Actualiza el estatus de solicitud y orden de compra a cancelado
     ********************************************************************/
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

                SolicitudesCompra::where('id', $id)->update(['estatus' => EstatusSolicitud::CANCELADA]);

                $cotizacion = Cotizaciones::where('solicitudes_compra_id', $id)->first();

                OrdenCompra::where('cotizaciones_id', $cotizacion->id)->update(['estatus' => EstatusOrdenCompra::CANCELADA]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Se ha realizado correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error inesperado',
                'error' => $e->getMessage()
            ]);

        }
    }

    public function rechazarOrdenCompra(Request $request)
    {
        $data = $request->all();

        try {
            DB::beginTransaction();

            $solicitud = SolicitudesCompra::find($data['id']);
            if ($solicitud) {
                $solicitud->estatus = EstatusSolicitud::CANCELADA;
                $solicitud->razon_cancelacion = $data['razonCancelacion'];
                $solicitud->save();
            }


            $cotizacion = Cotizaciones::where('solicitudes_compra_id', $data['id'])->first();


            if ($cotizacion) {
                $orden = OrdenCompra::where('cotizaciones_id', $cotizacion->id)->first();
                if ($orden) {
                    $orden->estatus = EstatusOrdenCompra::CANCELADA;
                    $orden->razon_cancelacion = $data['razonCancelacion'];
                    $orden->save();
                }
            }


            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Se ha realizado correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error inesperado',
                'error' => $e->getMessage()
            ]);

        }
    }

    public function enviarCorreoSurtido($idOrdenCompra, $rutaComPago = null){

        $this->ordenCompraService->enviarCorreoSurtido($idOrdenCompra, $rutaComPago);
    }

    /** ********************************************************************************
     * Marca la solicitud como autorizada y envía un correo al proveedor seleccionado
     **********************************************************************************/
    public function autorizarOrden(Request $request)
    {
        $data = $request->all();
        $idOc = $data['idOrdenCompra'];

        try {
            DB::beginTransaction();

            $orden = OrdenCompra::find($idOc);
            $this->actStatusOrdenSolicitud( $orden->id, EstatusOrdenCompra::AUTORIZADA, EstatusSolicitud::AUTORIZADA);

            if($orden->modo_pago == 1){
                $this->actStatusOrdenSolicitud( $orden->id, EstatusOrdenCompra::FACTURADO , EstatusSolicitud::FACTURADO);
            }else{
                $this->actStatusOrdenSolicitud($orden->id, EstatusOrdenCompra::EN_SURTIDO, EstatusSolicitud::EN_SURTIDO);
                $this->enviarCorreoSurtido($orden->id);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Se ha autorizado correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {

            DB::rollback();

            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error inesperado',
                'error' => $e->getMessage()
            ]);
        }
    }

    /** ************************************************************************************
     * Lee el contenido de archivos xml en el servidor y le envía los datos al frontend
     * Probablemente en desuso
     **************************************************************************************/
    public function leerXML($id)
    {
        // Recupera las ruta del xml en el servidor
        $rutas = DocumentosOrdenesCompra::where('orden_compra_id', $id)->get('ruta_xml_factura');

        $contenidosXML = [];
        // Recupera los contenido de los XML
         foreach($rutas as $ruta){
            $rutaXML =  storage_path('app/' . $ruta['ruta_xml_factura']);
            if (file_exists($rutaXML)) {
                $contenidoXML = file_get_contents($rutaXML);
                $contenidosXML[] = $contenidoXML;
            }
            else{
                return response()->json(['message' => 'Archivo no encontrado']);
            }
         }

        // Envía el contenido hacia el fronted en json
          return response()->json(['contenidos' => $contenidosXML], 200)
              ->header('Content-Type', 'application/json');
        // return $contenidosXML;

    }

    public function autorizarOrdenPago(Request $request){

        $data = $request->all();

        $orden = OrdenCompra::where('id', $data['id'])->first();
        if ($orden) {
            $orden->estatus = EstatusOrdenCompra::AUTORIZADO_A_PAGO;
            $orden->modo_pago = $data['modo_pago'];
            $orden->save();
        }

        $cotizacion = Cotizaciones::where('id', $orden->cotizaciones_id)->first();

        $solicitud = SolicitudesCompra::find($cotizacion->solicitudes_compra_id);
        if ($solicitud) {
            $solicitud->estatus = EstatusSolicitud::AUTORIZADO_A_PAGO;
            $solicitud->save();
            }

        if($orden->modo_pago == 1){
            $this->actStatusOrdenSolicitud( $data['id'], EstatusOrdenCompra::SOLICITADO_PAGO , EstatusSolicitud::SOLICITADO_PAGO);
        }

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Se ha solicitado el pago de la orden de compra'
        ]);

    }


    /** **************************************************************
     * Consulta para generar el formato interno de orden de compra
     ****************************************************************/
    public function consultaDatosPDF($id)
    {
        return $this->ordenCompraService->getDataOrdenCompra($id);

    }

    public function descargarOrdenCompra($id)
    {
        $file = $this->consultaDatosPDF($id);
        $fileName = ''.$file['folioOrdenCompra'].'_'. $file['folioSolicitudCompra'].'.pdf';
        return response($file['archivoPDF'], 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->header('X-Filename', $fileName)
            ->header('Access-Control-Expose-Headers', 'X-Filename');
    }


    public function previewOrdenCompra($id)
    {
        $file = $this->consultaDatosPDF($id);
        $fileName = ''.$file['folioOrdenCompra'].'_'. $file['folioSolicitudCompra'].'.pdf';
        return response($file['archivoPDF'], 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"')
            ->header('Cache-Control', 'no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('X-Filename', $fileName)
            ->header('Access-Control-Expose-Headers', 'X-Filename');
    }

    public function  solicitarSurtido(Request $request){
        $data = $request->all();
        $idOrdenCompra = $data['id_orden_compra'];
        if($data['tipo'] == 1){
            $this->actStatusOrdenSolicitud($idOrdenCompra, EstatusOrdenCompra::EN_SURTIDO, EstatusSolicitud::EN_SURTIDO);
            $this->enviarCorreoSurtido($idOrdenCompra);
            return response()->json([
                'status' => 'success',
                'data' => [],
                'message' => 'Se marco la solicitud como en surtido y se envió tu solicitud de surtido por correo al proveedor '
            ]);
        }else{
            $this->actStatusOrdenSolicitud($idOrdenCompra, EstatusOrdenCompra::EN_SURTIDO, EstatusSolicitud::EN_SURTIDO);
            return response()->json([
                'status' => 'success',
                'data' => [],
                'message' => 'Se ha macado la solicitud como en surtido, ponte en contacto con tu proveedor'
            ]);
        }
    }

    public function actStatusOrdenSolicitud($idOrdenCompra, $statusOrdenCompra, $estatusSolicitud){
        $this->statusService->actStatusOrdenSolicitud($idOrdenCompra, $statusOrdenCompra, $estatusSolicitud);
    }

    public function markAsFinalizada($idOrdenCompra){

        $this->actStatusOrdenSolicitud($idOrdenCompra, EstatusOrdenCompra::FINALIZADA, EstatusSolicitud::FINALIZADA);

        return response()->json([
            'status' => 'success',
            'data' => [],
            'message' => 'Se ha marcado la solicitud como Finalizada'
        ]);
    }

    public function cambiarProveedorSeleccionado(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->all();
            $idSolicitudCompra = $data['idSolicitudCompra'];
            $idCotizacionProveedor = $data['idCotProv'];
            $observaciones = $data['observaciones'];
            $lugarEntrega = $data['entrega'];
            $modoPago = $data['modoPago'];
            $fechaEntrega = $data['fechaEntrega'];

            $cotizacion = Cotizaciones::where('solicitudes_compra_id', $idSolicitudCompra)->first();
            if ($cotizacion) {
                // Quitar el proveedor seleccionado
                $this->cotizacionesService->desmarcarCotizacionSeleccionada($cotizacion->id);
                $this->cotizacionesService->cotizacionProveedorSeleccionada($idCotizacionProveedor);
                // Modificar la orden de compra que ya existía
                $ordenCompra =  OrdenCompra::where('cotizaciones_id', $cotizacion->id)->first();
                
                if ($ordenCompra) {
                    $this->ordenCompraService->actualizarOrdenCompra($ordenCompra, $data);
                }
            }
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'El cambio se realizo correctamente',
                'data' => []
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error inesperado',
                'error' => $e->getMessage()
            ]);
        }
    }
}
