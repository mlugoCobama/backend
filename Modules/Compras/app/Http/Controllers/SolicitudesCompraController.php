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
use Modules\Compras\Models\OrdenCompra;

//Transformers
use Modules\Compras\Transformers\DetalleSolicitudCompraResource;
use Modules\Compras\Transformers\SolicitudesComprasResource;
use Modules\Compras\Transformers\EmailAutorizarSolicitudResource;
use Modules\Compras\Transformers\SeguimientoResource;

//Utilities
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Helpers\NotificationHelper;

// Mailiables
use App\Mail\SolicitudCotizacion;
use App\Notifications\SolicitudCotizacionNotification;
use Modules\Compras\Notifications\AutorizacionEmail;
use Modules\Compras\Transformers\EmailAutorizarSolicitud;
// Jobs
use App\Jobs\EnviarCorreoSolicitudCotizacion;
use App\Notifications\CambioEstatusSolicitudCompra;
//Request validation
use Modules\Compras\Http\Requests\StoreSolicitudCompraRequest;
use Modules\Compras\Http\Requests\SendSolicitudCotizacionRequest;
use Modules\Compras\Models\ProveedorContacto;

class SolicitudesCompraController extends Controller
{

    /** ************************************************************
     * Recupera todos los registros de la base de datos
     **************************************************************/
    public function index(int $intercompania, ?int $id = null)
    {
        $usuariosCompra = explode(',', env('USERS_COMPRAS_COMPRAS')); 
        $isCompras = in_array($id, $usuariosCompra );

        $usuariosAdmin = explode(',', env('USERS_COMPRAS_ADMIN'));
        $isAdmin = in_array($id, $usuariosAdmin );

        $usuariosAdminz = explode(',', env('USERS_COMPRAS_ADMINZ'));
        $isAdminz = in_array($id, $usuariosAdminz );
        
        $usuariosRT = explode(',', env('USERS_COMPRAS_RT'));
        $isRT = in_array($id, $usuariosRT );

        $usuariosTG = explode(',', env('USERS_COMPRAS_TG'));
        $isTG = in_array($id, $usuariosTG );
        
        if($isRT){
            if(!in_array($id, [2395, 2404])){
                $query = $this->getSolicitudesCompras(null,0,0,3,$id,'rt');
            }else{
                $query = $this->getSolicitudesCompras(null,0,0,3,null,'rt');
            }
            $data = SolicitudesComprasResource::collection( $query );
            $tipo = 'RT';
        }

        if($isCompras){
            $query = $this->getSolicitudesCompras(null , 1, 1, 1, null, 'compras');
            $data = SolicitudesComprasResource::collection( $query );
            $tipo = 'compras';
        }

        if($isAdmin){
            $query = $this->getSolicitudesCompras(null , 0, 0, null, null, 'admin');
            $data = SolicitudesComprasResource::collection($query);
            $tipo = 'compras';
        }

        if($isAdminz){
            $query = $this->getSolicitudesCompras(null , 1, 1, null, null, 'adminz');
            $data = SolicitudesComprasResource::collection($query);
            $tipo = 'compras';
        }

        if($isTG){
            $query = $this->getSolicitudesCompras(null, 0, 0, 1, 394, 'empresa');
            $data = SolicitudesComprasResource::collection( $query);
            $tipo = 'empresa';
        }
        
        if(!$isRT && !$isCompras && !$isAdmin  && !$isTG && !$isAdminz){
            $query = $this->getSolicitudesCompras($intercompania , 0, 0, null, null, 'empresa');
            $data = SolicitudesComprasResource::collection(
                $query
            );
            $tipo = 'empresa';
        }
        
        return response()->json([
            'tipo' => $tipo,
            'status' => 'success',
            'message' => 'Consulta generada correctamente',
            'data' => $data
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

            $solicitud = $this->storeSolicitudCompra($data);
            $this->storeDetalleSolicitudCompra($data['detalles'], $solicitud['id'], $files, (int)$solicitud['destino'] );
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
        $solicitud = SolicitudesCompra::find($id);
        $campo = $request->campo;
        $valor = $request->value;
        $solicitud->$campo = $valor;
        $solicitud->save();

        $this->validarCotizacion($id);
        
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

        $solicitudCompra = SolicitudesCompra::find($id);

        if (!$solicitudCompra) {
            return response()->json([
                'status' => 'error',
                'message' => 'El registro que intentas actualizar no existe',
                'data' => []
            ]);
        }

        $solicitudCompra->update([
            'estatus' => EstatusSolicitud::CANCELADA
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Se ha eliminado correctamente',
            'data' => []
        ]);
    }


    public function cancelarSolicitud(Request $request)
    {
        $data = $request->all();

        $solicitudCompra = SolicitudesCompra::find($data['id'] ?? $data[0]);

        if (!$solicitudCompra) {
            return response()->json([
                'status' => 'error',
                'message' => 'El registro que intentas actualizar no existe',
                'data' => []
            ]);
        }

        $solicitudCompra->estatus = EstatusSolicitud::CANCELADA;
        $solicitudCompra->razon_cancelacion = $data['razonCancelacion'] ?? null;
        $solicitudCompra->save();

        $cotizacion = Cotizaciones::where('solicitudes_compra_id', $solicitudCompra->id)->first();
        if($cotizacion){
            $ordenCompra = OrdenCompra::where('cotizaciones_id', $cotizacion->id)->first();
            if($ordenCompra){
                $ordenCompra->estatus = EstatusOrdenCompra::CANCELADA;
                $ordenCompra->razon_cancelacion = $data['razonCancelacion'] ?? null;
                $ordenCompra->save();
            }
        }   

        NotificationHelper::sendNotificationEstatusChange($solicitudCompra->id, 'Cancelada: Razón - '. $solicitudCompra->razon_cancelacion);

        return response()->json([
            'status' => 'success',
            'message' => 'Se ha eliminado correctamente',
            'data' => []
        ]);
    }

    /** *********************************************************** 
     * Genera un nuevo folio consecutivo en base al ultimo folio
     *************************************************************/
    public function generarFolioSc()
    {
        $ultimaOrden = SolicitudesCompra::administrador()->orderBy('id', 'desc')
        // ->active()
        ->first('folio');
        if ($ultimaOrden) {
            $ultimoFolio = $ultimaOrden->folio;
            $numero = intval(substr($ultimoFolio, 3)) + 1;
        } else {
            $numero = 1;
        }
        $nuevoFolio = 'SC-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
        return  $nuevoFolio;
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

    /** ************************************************************
     * Recupera todos los registros de la base de datos
     * Con paginacion (30 registros por pagina)
     ***************************************************************/
    public function index1(Request $request)
    {

        $perPage = $request->input('perPage', 30);

        $solicitudes = (SolicitudesCompra::active()
            ->paginate($perPage));
        return response()->json([
            'data' => $solicitudes->items(),
            'pagination' => [
                'current_page' => $solicitudes->currentPage(),
                'last_page' => $solicitudes->lastPage(),
                'per_page' => $solicitudes->perPage(),
                'total' => $solicitudes->total(),
            ]
        ]);
    }

    /** ********************************************************************
     *Primero genero una solicitud de compra
     *Después almaceno los detalles de la solicitud
     **********************************************************************/
    private function storeSolicitudCompra($data)
    {

        $usuariosRT = explode(',', env('USERS_COMPRAS_RT'));
        $isInfra = in_array($data["usuario_solicita"], $usuariosRT );

        $usuariosSopGas = explode(',', env('USERS_SOP_GAS'));
        $isSopGas = in_array($data["usuario_solicita"], $usuariosSopGas );

        $dataSolicitud = new SolicitudesCompra();
        $dataSolicitud->folio = $this->generarFolioSc();
        $dataSolicitud->usuario_solicita = $data["usuario_solicita"];
        $dataSolicitud->empresa = $data["empresa"];
        $dataSolicitud->usuario_destino = $data["usuario_destino"];
        $dataSolicitud->motivo = $data["motivo"];
        $dataSolicitud->fecha = date('Y-m-d H:i:s') ?? now();
        $dataSolicitud->c_c = $data["c_c"];
        $dataSolicitud->requiere_anticipo =  ($data["requiere_anticipo"] === "true") ? 1 : 0 ;
                
        if($isInfra){
           $dataSolicitud->tipo = 3;
           $dataSolicitud->auto_admin =  $isSopGas  ? 0 : 1;
           $dataSolicitud->auto_gg = $isSopGas  ? 0 : 1;
           $dataSolicitud->auto_macro = 1;
           $dataSolicitud->estatus = $isSopGas ? 1 : 2;
           $dataSolicitud->com_cat_sistemas_auto_id = $data["sistema"];
           $dataSolicitud->com_cat_tipos_mantenimiento_id = $data["tipoMantenimiento"];
        }

        $dataSolicitud->save();
        return [
            'id' => $dataSolicitud->id,
            'destino' => $data["usuario_destino"]
            ];
    }

    /** ***************************************************************************
     * Amacena los detalles de la solicitud
     *****************************************************************************/
    private function storeDetalleSolicitudCompra($detalles, $idSolicitud, $files, $destino)
    {
        foreach ($detalles as $index => $detalle) {
            $detalleSolicitud = new DetalleSolicitud();
            $detalleSolicitud->cantidad = $detalle["cantidad"];
            $detalleSolicitud->descripcion = $detalle["descripcion"];
            $detalleSolicitud->observaciones = $detalle["observaciones"];
            $detalleSolicitud->cat_unidades_medida_id = $detalle["cat_unidades_medida_id"];
            $detalleSolicitud->recuperable = $detalle["recuperar_costo"];

            // Maneja el archivo de imagen
            $fileKey = "img_referencia_" . $index;
            if (isset($files[$fileKey]) && $files[$fileKey]->isValid()) {
                if (in_array($files[$fileKey]->extension(), ['jpg','jpeg','png'])) {
                    $path = $files[$fileKey]->store('referencias', 'public');
                    $detalleSolicitud->img_referencia = $path;
                }else {
                    throw new \Exception("El archivo debe ser una imagen JPG o PNG");
                }
            }



            $detalleSolicitud->solicitudes_compra_id = $idSolicitud;
            $detalleSolicitud->save();
        }
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

            // Obtener los proveedores completos desde la base de datos
            $items = collect($data['proveedores']);
            $idSolicitudC = $data['solicitudes_compra_id'];
            $proveedoresIds = $items->pluck('proveedor_id')->unique();

            $proveedores = Proveedores::whereIn('id', $proveedoresIds)
            ->with('contactos')
            ->get()
            ->keyBy('id');
            // Verificar que todos los proveedores tengan correo asignado
            if ($error = $this->validateProveedoresConCorreo($proveedores)) {
                return $this->errorResponse($error);
            }

            if ($error = $this->validateContactosConCorreo($items, $proveedores)) {
                return $this->errorResponse($error);
            }

            $cotizacion = Cotizaciones::where('solicitudes_compra_id', $data['solicitudes_compra_id'])->first();
            if($cotizacion){
                $idCotizacion = $cotizacion->id;
                
            }else{
                $idCotizacion = $this->storeCotizacion($data);
                $solicitud = SolicitudesCompra::find($idSolicitudC);
                $solicitud->estatus = EstatusSolicitud::EN_COTIZACION;
                $solicitud->save();
            }

            $this->storeCotizacionProveedores($items, $idCotizacion);
            //Queue para despachar el correo EnviarCorreoSolicitudCotizacion::dispatch($data)
            $this->enviaCorreoProveedores($items, $data);
            
            DB::commit();

            NotificationHelper::sendNotificationEstatusChange($idSolicitudC, 'En Cotización');

            return response()->json([
                'status' => 'success',
                'message' => 'Correos enviados correctamente',
                'data' => [
                    'cotizacion_id' => $idCotizacion,
                    'proveedores_count' => $proveedores->count(),
                    'solicitud_id' => $idSolicitudC
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return $this->errorResponse('Ocurrió un error al procesar la solicitud', $e->getMessage(), $data);
        }
    }

    /** ***********************************************************************
     * Función que genera folios consecutivos de las cotizaciones
     ************************************************************************/
    public function generarFolioCo()
    {
        $ultimaCotizacion = Cotizaciones::orderBy('id', 'desc')->first('folio');
        if ($ultimaCotizacion) {
            $ultimoFolio = $ultimaCotizacion->folio;
            $numero = intval(substr($ultimoFolio, 3)) + 1;
        } else {
            $numero = 1;
        }
        $nuevoFolio = 'CO-' . str_pad($numero, 5, '0', STR_PAD_LEFT);

        return $nuevoFolio;
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

    /** 
     * Verifica si ya esta autorizado por gerencias 
     * y actualiza el estatus a siguiente
     */
    public function validarAutorizacion($id)
    {
        $solicitud = SolicitudesCompra::findOrFail($id);
        if ($solicitud->auto_admin === 1 && $solicitud->auto_gg === 1 && $solicitud->tipo === 1) {
            $solicitud->estatus = EstatusSolicitud::SOLICITADO;
            NotificationHelper::sendNotificationEstatusChange($solicitud->id, 'Solicitado');
            $solicitud->save();
        }

        if ($solicitud->auto_admin === 1 && $solicitud->auto_gg 
            && $solicitud->auto_macro === 1 && $solicitud->tipo === 2) {
            $solicitud->estatus = EstatusSolicitud::SOLICITADO;
            NotificationHelper::sendNotificationEstatusChange($solicitud->id, 'Solicitado');
            $solicitud->save();
        }
    }

    public function validarCotizacion($id)
    {
        $cotizacion = Cotizaciones::where('solicitudes_compra_id', $id)->first();

        if(!empty($cotizacion)){
            $cotizacionesDisponibles = CotizacionesProveedores::where('cotizaciones_id', $cotizacion->id)->get();
            $solicitud = SolicitudesCompra::findOrFail($id);
            if ($solicitud->auto_admin === 1 && $solicitud->auto_gg === 1 ) {
            $solicitud->estatus = EstatusSolicitud::EN_COTIZACION;
            $solicitud->save();
            }

            if (!empty($cotizacionesDisponibles)) {
                foreach ($cotizacionesDisponibles as $cotizacion) {
                    $modelo = CotizacionesProveedores::find($cotizacion->id);
                    if ($modelo && $modelo->ruta !== null) {
                        $modelo->autorizado = 1;
                        $modelo->save();
                    }
                }
            }
        }
    }

    /** ***********************************************************************
     * Almacena la cotización y devuelve el id del registro creado
     ************************************************************************/
    public function storeCotizacion($data)
    {
        $dataCotizacion = new Cotizaciones();
        $dataCotizacion->folio = $this->generarFolioCo();
        $dataCotizacion->fecha = now();
        $dataCotizacion->consideraciones = $data["consideraciones"] ?? null;
        $dataCotizacion->solicitudes_compra_id = $data["solicitudes_compra_id"];
        $dataCotizacion->save();
        
        return $dataCotizacion->id;
    }

    /** ***************************************************************************
     * Función que almacena la relación entre cotización y proveedores
     *****************************************************************************/
    public function storeCotizacionProveedores($proveedores, $idCotizacion)
    {
        $idsCotProv = [];
        
        foreach ($proveedores as $proveedor) {
            $datacotProv = new CotizacionesProveedores();
            $datacotProv->proveedores_id = $proveedor['proveedor_id'];
            $datacotProv->cotizaciones_id = $idCotizacion;
            $datacotProv->contacto_id = $proveedor['contacto_id'];
            $datacotProv->save();
            
            $idsCotProv[] = $datacotProv->id;
        }
        
        return $idsCotProv;
    }

    /** ***************************************************************************
     * Función que  envía el correo de solicitud de cotización a los proveedores
     ****************************************************************************/
    public function enviaCorreoProveedores($proveedores, $data)
    {
        $data['proveedores'] = $proveedores->toArray();
        $data['detalles'] = DetalleSolicitud::where("solicitudes_compra_id", $data['solicitudes_compra_id'])->confirmadas()->get();
        $data['solicitudCompra'] = SolicitudesCompra::find($data['solicitudes_compra_id']);
        
        foreach ($proveedores as $proveedor) {
            $correo = '';

            if(!empty($proveedor['contacto_id'])){
                $correo = ProveedorContacto::find($proveedor['contacto_id']);
            }else{
                $correo = Proveedores::find($proveedor['proveedor_id']);
            }

            if (!empty($correo->correo)) {
                    try {
                        // Notification::route('mail', $proveedor->correo)
                        //     ->notify(new SolicitudCotizacionNotification($data));
                        Mail::to($correo->correo)->send(new SolicitudCotizacion($data));

                    } catch (\Exception $e) {
                        // \Log::error("Error al enviar correo a proveedor {$proveedor->id}: " . $e->getMessage());
                    }
                }
        }
    }

    public function reenviarCorreo(Request $request){
        $data = $request->all();
        $cotizacionProveedor = CotizacionesProveedores::
        // with(['proveedor', 'contacto'])
        find($data['id']);

        $proveedores = collect([[
            'proveedor_id' => $cotizacionProveedor->proveedor_id,
            'contacto_id'  => $cotizacionProveedor->contacto_id
        ]]);

        $this->enviaCorreoProveedores( $proveedores, $data);
        return response()->json([
            'data' => [],
            'status' => 'success',
            'message' => 'Correo re enviado correctamente'
        ]);
    }

    /**
     * Genera los detalles de cotización y les asigna un cero por defecto
     */
    public function storeDetallesCotizacion($data, $idsCotProv)
    {
        foreach ($data['detalles'] as $detalle) {
            foreach ($idsCotProv as $idDataCotProv) {
                $detalleCotizacion = new DetallesCotizacion();
                $detalleCotizacion->detalle_solicitud_id = $detalle['id'];
                $detalleCotizacion->cotizaciones_proveedores_proveedores_id = $idDataCotProv;
                //$detalleCotizacion->precio_unitario = $detalle['precio_unitario'] ?? 0; 
                $detalleCotizacion->save();
            }
        }
    }


    /**
     * Envía el correo de solicitud de autorización
     * agrega los parámetros para la url del botón 'Autorizo'
     */
    public function sendSolicitudAutorizacion($id, $correos)
    {
        $data = (new EmailAutorizarSolicitudResource(SolicitudesCompra::findOrFail($id)))->resolve();
        $data['autoNecesarias'] = $correos['autoNecesarias'];
        foreach ($correos['data'] as $correo) {
            $email = $correo['name'];
            $data['campo'] = $correo['campo'];
            Notification::route('mail', $email)
                ->notify(new AutorizacionEmail($data));
        }
    }

    /**
     * Recupera los datos de los gerente de las empresas en base a 
     * su numero de intercompania 
     */
    private function getGerente($intercompania)
    {
        $interAgencias = array_flip([7064, 7063, 7062, 7061]);
        $isRenault = isset($interAgencias[$intercompania]);
        if ($isRenault) {
            $intercompania = 7064;
        }
        $data = DB::connection('intranet')->select('call SOPORTEZM.SP_GetGereneciaEmpresas(' . $intercompania . ')');
        $subCadena = 'gerencia';
        $autoNecesarias  = count($data);
        if ($autoNecesarias > 0) {
            foreach ($data as $dato) {
                $isGerente = strpos($dato->name, $subCadena);
                if ($isGerente !== false) {
                    $dato->isGerente = true;
                    $dato->campo = 'gg';
                } else {
                    $dato->isGerente = $isGerente;
                    $dato->campo = 'admin';
                }
            }
            return [
                'data' =>  $data,
                'autoNecesarias' => $autoNecesarias
            ];
        }
    }

    /**
     * Recupera las solicitudes de compra con el folio y total de la orden de compra
     * 
     * @param mixed $intercompania Numero de intercompania de la empresa
     * @param mixed $autoga Autorizacion de gerencia administrativa (0 o 1)
     * @param mixed $autogg Autorizacion de gerencia (0 o 1)
     * @param mixed $tipoSolicitud tipo de solicitud (1= compras, 2 = rt, null = ambas)
     * @param mixed $idUserObjetivo usuario objetivo (null = no aplica el filtro)
     */
    public function getSolicitudesCompras($intercompania, $autoga, $autogg, $tipoSolicitud, $idUserObjetivo, $tipoUsuario){
        return DB::select('CALL SP_GetSolicitudesComprasTesting(?, ?, ?, ?, ?, ?)', [ $intercompania , $autoga, $autogg, $tipoSolicitud, $idUserObjetivo, $tipoUsuario]);
    }


    /**
     * Recupera lo eventos de actualización de estatus de la solicitud de compra
     * @param mixed $idSolicitud id de solicitud de orden de compra
     */
    public function getSeguimientoSolicitud($idSolicitud){
        $eventos = LogEventos::where('table_name', 'com_solicitudes_compra')->where('record_id', $idSolicitud)->get();

        return response()->json([
            'status' => 'success',
            'data' => SeguimientoResource::collection($eventos),
            'message' => 'datos recuperados correctamente'
        ]);
    }

    private function validateProveedoresConCorreo($proveedores)
    {
        $sinCorreo = $proveedores->filter(fn($p) => empty($p->correo));
        return $sinCorreo->isNotEmpty()
            ? 'Algunos proveedores no tienen correo: ' . $sinCorreo->pluck('nombre')->implode(', ')
            : null;
    }

    private function validateContactosConCorreo($items, $proveedores)
    {
        $sinCorreo = $items->filter(function ($item) use ($proveedores) {
            if (!$item['contacto_id']) return false;
            $contacto = $proveedores[$item['proveedor_id']]->contactos
                ->firstWhere('id', $item['contacto_id']);
            return empty($contacto?->correo);
        });
        return $sinCorreo->isNotEmpty()
            ? 'Algunos contactos no tienen correo asignado'
            : null;
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


}
