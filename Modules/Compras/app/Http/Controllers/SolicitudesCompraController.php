<?php

namespace Modules\Compras\Http\Controllers;

use App\Enums\EstatusOrdenCompra;
use App\Http\Controllers\Controller;
use App\Enums\EstatusSolicitud;
use Illuminate\Http\Request;
use App\Models\LogEventos;

//Models
use Modules\Compras\Models\SolicitudesCompra;
use Modules\Compras\Models\DetalleSolicitud;
use Modules\Compras\Models\Cotizaciones;
use Modules\Compras\Models\CotizacionesProveedores;
use Modules\Compras\Models\DetallesCotizacion;
use Modules\Compras\Models\Proveedores;

//Transformers
use Modules\Compras\Transformers\DetalleSolicitudCompraResource;
use Modules\Compras\Transformers\SolicitudesComprasResource;
use Modules\Compras\Transformers\SeguimientoResource;

//Utilities

use Illuminate\Support\Facades\DB;

use App\Helpers\NotificationHelper;

// Mailiables

//Request validation
use Modules\Compras\Http\Requests\StoreSolicitudCompraRequest;
use Modules\Compras\Http\Requests\SendSolicitudCotizacionRequest;
use Modules\Compras\Services\ComprasGeneralesService;
use Modules\Compras\Services\CotizacionesService;

class SolicitudesCompraController extends Controller
{
    protected $compGneralesService;
    protected $cotizacionesService;

    public function __construct(
        CotizacionesService $cotizacionesService,
        ComprasGeneralesService $compGneralesService
    ) {
        $this->cotizacionesService = $cotizacionesService;
        $this->compGneralesService = $compGneralesService;
    }
    /** ************************************************************
     * Recupera todos los registros de la base de datos
     **************************************************************/
    public function index(Request $request)
    {


        $usuariosCompra = config('compras.users.compras', []);
        $isCompras = in_array($request->id, $usuariosCompra);

        $usuariosAdmin = config('compras.users.admin', []);
        $isAdmin = in_array($request->id, $usuariosAdmin);

        $usuariosAdminz = config('compras.users.adminz', []);
        $isAdminz = in_array($request->id, $usuariosAdminz);

        $usuariosRT = config('compras.users.rt', []);
        $isRT = in_array($request->id, $usuariosRT);

        $usuariosComprasAutos = config('compras.users.autos', []);
        $isComprasAutos = in_array($request->id, $usuariosComprasAutos);

        $usuariosTG = config('compras.users.tg', []);
        $isTG = in_array($request->id, $usuariosTG);

        $usuariosAutosExcepcion = config('compras.excepciones.autos', []);
        $isExepcion = in_array($request->id, $usuariosAutosExcepcion);

        if($isRT){
            if(!in_array($request->id, [2395])){
                $query = $this->compGneralesService->getSolicitudesCompras($request->intercompania,0,0,3,$request->id,'rt', $request->anio, $request->mes);
            }else{
                $query = $this->compGneralesService->getSolicitudesCompras($request->intercompania,0,0,3,null,'rt', $request->anio, $request->mes);
            }
            $tipo = 'RT';
        }

        if($isCompras){
            $query = $this->compGneralesService->getSolicitudesCompras($request->intercompania, 1, 1, 1, $request->id, 'compras', $request->anio, $request->mes);
            $tipo = 'compras';
        }

        if($isComprasAutos){
            $query = $this->compGneralesService->getSolicitudesCompras($request->intercompania, 1, 1, 4, null, 'comprasAutos', $request->anio, $request->mes);
            $tipo = 'compras';
        }

        if($isAdmin){
            $query = $this->compGneralesService->getSolicitudesCompras($request->intercompania, 0, 0, null, null, 'admin', $request->anio, $request->mes);
            $tipo = 'compras';
        }

        if($isAdminz){
            $query = $this->compGneralesService->getSolicitudesCompras($request->intercompania, 1, 1, null, null, 'adminz', $request->anio, $request->mes);
            $tipo = 'compras';
        }

        if($isTG){
            $query = $this->compGneralesService->getSolicitudesCompras($request->intercompania, 0, 0, 1, 394, 'empresa', $request->anio, $request->mes);
            $tipo = 'empresa';
        }

        if(!$isRT && !$isCompras && !$isAdmin  && !$isTG && !$isAdminz && !$isComprasAutos){
            if(!$isExepcion){
                $query = $this->compGneralesService->getSolicitudesCompras($request->intercompania , 0, 0, null, null, 'empresa', $request->anio, $request->mes);
            }else{
               $query = $this->compGneralesService->getSolicitudesCompras($request->intercompania , 0, 0, null, $request->id, 'empresa', $request->anio, $request->mes);
            }
            $tipo = 'empresa';
        }

        return response()->json([
            'tipo' => $tipo,
            'status' => 'success',
            'message' => 'Consulta generada correctamente',
            'data' => SolicitudesComprasResource::collection($query)
        ]);
    }

    /** *************************************************************************************
     * Genera un el registro de la solicitud de compra junto con sus detalles
     * Valida y coordina el funcionamiento de storeSolicitudCOmpra y storeDetallesSolicitud
     ***************************************************************************************/
    public function store(StoreSolicitudCompraRequest $request)
    {
        $data =  $request->validated()['data'];
        $files = $request->allFiles();

        try {
            DB::beginTransaction();

            $solicitud = $this->compGneralesService->storeSolicitudCompra($data);
            $this->compGneralesService->storeDetalleSolicitudCompra($data['detalles'], $solicitud['id'], $files, (int)$solicitud['destino'] );
            //TODO MODIFICAR ESTO PARA QUE SE ENVIÉ EL CORREO
            //$correos = $this->getGerente($data['empresa']);
            //?$this->sendSolicitudAutorizacion($solicitud['id'], $correos);
            DB::commit();
            NotificationHelper::sendNotificationEstatusChange($solicitud['id'], 'Solicitud creada');

            return response()->json([
                'status' => 'success',
                'message' => 'Se ha guardado correctamente',
                'data' => $solicitud
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al guardar la solicitud' . ($e->getMessage() ?? ''),
                'error' => $e->getMessage()
            ]);
        }
    }


    /** *******************************************************************
     * Recupera los detalles de la solicitud
     *********************************************************************/
    public function show($id)
    {
        return DetalleSolicitudCompraResource::collection((DetalleSolicitud::where('solicitudes_compra_id', $id)->with('unidadMedida')->get()));
    }


    /**
     * Actualiza los estatus de la solicitud de compra
     * auto_gg, auto_admin y estatus
     */
    public function update(Request $request, $id)
    {
        $allowedFields = ['auto_admin', 'auto_gg', 'auto_macro', 'estatus', 'observaciones', 'motivo_revision'];
        $campo = $request->input('campo');

        if (!in_array($campo, $allowedFields, true)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Campo no permitido para actualización directa',
                'data'    => []
            ], 422);
        }

        $solicitud = SolicitudesCompra::find($id);
        if (!$solicitud) {
            return response()->json([
                'status'  => 'error',
                'message' => 'La solicitud no existe',
                'data'    => []
            ], 404);
        }

        $solicitud->$campo = $request->input('value');
        $solicitud->save();

        $this->cotizacionesService->validarCotizacion($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Se ha actualizado correctamente',
            'data' => []
        ]);
    }

    /** ************************************************************
     * Actualiza el estatus a cancelado
     **************************************************************/
    public function destroy($id)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Se ha eliminado correctamente',
            'data' => []
        ]);
    }


    public function cancelarSolicitud(Request $request)
    {
        $data = $request->all();

        $this->compGneralesService->cancelarSolicitud($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Se ha eliminado correctamente',
            'data' => []
        ]);
    }


    /** *******************************************************************
     * Recupera las solicitud de compra por id
     *********************************************************************/
    public function getSolicitud($id)
    {
        $data = new SolicitudesComprasResource(SolicitudesCompra::findOrFail($id));

        return response()->json([
            'status' => 'success',
            'message' => 'Consulta generada correctamente',
            'data' => $data
        ]);
    }


    /** ******************************************************************
     * Envía la solicitud de cotización a los proveedores y almacena la
     * relación en la BD
     ********************************************************************/
    public function enviarSolicitudCotizacion(SendSolicitudCotizacionRequest $request)
    {
        $data = $request->validated();
        try {
            DB::beginTransaction();
               $resultado = $this->cotizacionesService->enviarSolicitudCotizacion($data);
            DB::commit();
            NotificationHelper::sendNotificationEstatusChange( $resultado['solicitud_id'], 'En Cotización');
            return response()->json([
                'status' => 'success',
                'message' => 'Correos enviados correctamente',
                'data' =>  $resultado
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Ocurrió un error al procesar la solicitud', $e->getMessage(), $data);
        }
    }

    /**
     * Recupera las autorizaciones por medio del correo
     * Valida el numero de autorizaciones necesarias
     * Modifica los campos necesarios en la base de datos
     */
    public function autorizeFromEmail($campo, $necesarias, $id)
    {
        $campoBD = "auto_$campo";
        $solicitud = SolicitudesCompra::findOrFail($id);
        if ($necesarias === 1) {
            $solicitud->auto_admin = "1";
            $solicitud->auto_gg = "1";
            $solicitud->save();
        } else {
            $solicitud->$campoBD = "1";
            $solicitud->save();
        }
        $this->validarAutorizacion($id);

        return view('compras::confirmacion');
    }


    public function reenviarCorreo(Request $request){
        $data = $request->all();
        $cotizacionProveedor = CotizacionesProveedores::find($data['id']);

        $proveedores = collect([[
            'proveedor_id' => $cotizacionProveedor->proveedores_id,
            'contacto_id'  => $cotizacionProveedor->contacto_id
        ]]);

        $this->cotizacionesService->enviaCorreoProveedores($proveedores, $data);
        return response()->json([
            'data' => [],
            'status' => 'success',
            'message' => 'Correo re enviado correctamente'
        ]);
    }

    /**
     * Recupera lo eventos de actualización de estatus de la solicitud de compra
     * @param mixed $idSolicitud id de solicitud de orden de compra
     */
    public function getSeguimientoSolicitud($idSolicitud){
        $eventos = LogEventos::where('table_name', 'com_solicitudes_compra')->where('record_id', (int)$idSolicitud)->get();

        return response()->json([
            'status' => 'success',
            'data' => SeguimientoResource::collection($eventos),
            'message' => 'datos recuperados correctamente'
        ]);
    }


    private function errorResponse($message, $error = null, $data = [])
    {
        DB::rollBack();
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'error'   => $error,
            'data'    => $data
        ], 422);
    }


    public function devolverSolicitudEstatusAnterior(Request $request)
    {
        $solicitud = $this->compGneralesService->devolverSolicitudEstatusAnterior($request);

        return response()->json([
            'status'  => 'success',
            'message' => 'Solicitud actualizada correctamente',
            'data'    => $solicitud
        ]);
    }

    public function actualizarSolicitudCompra(Request $request){
        $data = json_decode($request->input('data'), true);

        $files = $request->allFiles();

        try {
                DB::beginTransaction();

                $this->compGneralesService->updateSolicitudCompra($data, $data['idSolicitud'] );
                $this->compGneralesService->updateDetalleSolicitudCompra($data['detalles'],$data['idSolicitud'], $files, (int)$data['usuario_destino'] );
                DB::commit();
                NotificationHelper::sendNotificationEstatusChange($data['idSolicitud'], 'Solicitud modificada-revisa si es necesario realizar alguna acción (Autorizar nuevamente la solicitud)');

                return response()->json([
                    'status' => 'success',
                    'message' => 'Se ha guardado correctamente',
                    'data' => $data
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ocurrió un error al guardar la solicitud' . ($e->getMessage() ?? ''),
                    'error' => $e->getMessage()
                ]);
            }

    }


}
